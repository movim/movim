<?php

namespace Movim\Daemon;

use App\Cache;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Symfony\Component\Console\Input\InputInterface;
use Dflydev\FigCookies\Cookies;

use Movim\Daemon\Session;

use App\Session as DBSession;
use App\EncryptedPassword;

use Minishlink\WebPush\VAPID;

class Core implements MessageComponentInterface
{
    public $sessions = [];
    private $input;
    private $key; // Random key generate by the daemon to authenticate the internal Websockets

    public $loop;
    public $baseuri;

    public $single = ['visio'];
    public $singlelocks = [];

    public function __construct($loop, $baseuri, InputInterface $input)
    {
        $this->input = $input;
        $this->key = \generateKey(32);

        $this->setWebsocket($this->input->getOption('port'));

        $this->loop    = $loop;
        $this->baseuri = $baseuri;

        DBSession::whereNotNull('id')->delete();

        // API_SOCKET ?
        if (file_exists(CACHE_PATH . 'socketapi.sock')) {
            unlink(CACHE_PATH . 'socketapi.sock');
        }

        array_map('unlink', array_merge(
            glob(PUBLIC_CACHE_PATH . '*.css'),
            glob(PUBLIC_CACHE_PATH . '*.js')
        ));

        $this->registerCleaner();

        // Generate Push Notification
        if (!file_exists(CACHE_PATH . 'vapid_keys.json')) {
            echo colorize("Generate and store the Push Notification VAPID keys", 'green')."\n";
            $keyset = VAPID::createVapidKeys();
            file_put_contents(CACHE_PATH . 'vapid_keys.json', json_encode($keyset));
        }
    }

    public function setWebsocket($port)
    {
        echo
            "\n".
            "--- ".colorize("Server Configuration - Apache", 'purple')." ---".
            "\n";
        echo colorize("Enable the Secure WebSocket to WebSocket tunneling", 'yellow')."\n# a2enmod proxy_wstunnel \n";
        echo colorize("Add this in your configuration file (default-ssl.conf)", 'yellow')."\nProxyPass /ws/ ws://127.0.0.1:{$port}/\n";

        echo
            "\n".
            "--- ".colorize("Server Configuration - nginx", 'purple')." ---".
            "\n";
        echo colorize("Add this in your configuration file", 'yellow')."\n";
        echo "location /ws/ {
    proxy_pass http://127.0.0.1:{$port}/;
    proxy_http_version 1.1;
    proxy_set_header Upgrade \$http_upgrade;
    proxy_set_header Connection \"Upgrade\";
    proxy_set_header Host \$host;
    proxy_set_header X-Real-IP \$remote_addr;
    proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto https;
    proxy_redirect off;
}

";
    }

    public function onOpen(ConnectionInterface $conn)
    {
        // WebSockets from the Browser
        $sid = $this->getSid($conn);
        if ($sid != null) {
            $path = $this->getPath($conn);

            if (in_array($path, $this->single)) {
                if (array_key_exists($sid, $this->singlelocks)
                && array_key_exists($path, $this->singlelocks[$sid])) {
                    $this->singlelocks[$sid][$path]++;
                    $conn->close(1008);
                } else {
                    $this->singlelocks[$sid][$path] = 1;
                }
            }

            if (!array_key_exists($sid, $this->sessions)) {
                $language = $this->getLanguage($conn);
                $offset = $this->getOffset($conn);

                $this->sessions[$sid] = new Session(
                    $this->loop,
                    $sid,
                    $this->baseuri,
                    $this->input->getOption('port'),
                    $this->key,
                    $language,
                    $offset,
                    $this->input->getOption('verbose'),
                    $this->input->getOption('debug')
                );
            }

            $this->sessions[$sid]->attach($conn);
        } else {
            // WebSocket from the internal subprocess
            $sid = $this->getHeaderSid($conn);
            if ($sid != null && isset($this->sessions[$sid])) {
                $this->sessions[$sid]->attachInternal($conn);

                $obj = new \StdClass;
                $obj->func = 'started';
                $this->sessions[$sid]->messageOut(json_encode($obj));
            }
        }
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $sid = $this->getSid($from);
        if ($sid != null && isset($this->sessions[$sid])) {
            $this->sessions[$sid]->messageIn($msg);
        } else {
            $sid = $this->getHeaderSid($from);
            if ($sid != null && isset($this->sessions[$sid])) {
                $this->sessions[$sid]->messageOut($msg);
            }
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $sid = $this->getSid($conn);

        if ($sid != null && isset($this->sessions[$sid])) {
            $path = $this->getPath($conn);

            if (in_array($path, $this->single)) {
                if (array_key_exists($sid, $this->singlelocks)
                && array_key_exists($path, $this->singlelocks[$sid])) {
                    $this->singlelocks[$sid][$path]--;
                    if ($this->singlelocks[$sid][$path] == 0) {
                        unset($this->singlelocks[$sid][$path]);
                    }
                }
            }

            $this->sessions[$sid]->detach($this->loop, $conn);
            if ($this->sessions[$sid]->process == null) {
                unset($this->sessions[$sid]);
            }
        }
    }

    public function forceClose($sid)
    {
        if (array_key_exists($sid, $this->sessions)) {
            $this->sessions[$sid]->killLinker();
            unset($this->sessions[$sid]);
        }
    }

    private function registerCleaner()
    {
        $this->loop->addPeriodicTimer(5, function () {
            foreach ($this->sessions as $sid => $session) {
                if ($session->countClients() == 0
                && $session->registered == null) {
                    $session->killLinker();
                }

                if ($session->process == null) {
                    unset($this->sessions[$sid]);
                }
            }

            $this->cleanupDBSessions();
            $this->cleanupEncryptedPasswords();
        });
    }

    private function cleanupDBSessions()
    {
        DBSession::where('active', false)
            ->where('created_at', '<', date(MOVIM_SQL_DATE, time()-60))
            ->delete();
    }

    private function cleanupEncryptedPasswords()
    {
        // Delete encrypted passwords after 7 days without update
        EncryptedPassword::where('updated_at', '<', date(MOVIM_SQL_DATE, time()-(60*60*24*7)))
            ->delete();
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";
    }

    public function getSessions()
    {
        return array_map(
            function ($session) {
                return $session->started;
            },
            $this->sessions
        );
    }

    public function getSession($sid)
    {
        if (isset($this->sessions[$sid])) {
            return $this->sessions[$sid];
        }
    }

    private function getLanguage(ConnectionInterface $conn)
    {
        $languages = $conn->httpRequest->getHeader('Accept-Language');
        return (is_array($languages) && !empty($languages)) ? $languages[0] : false;
    }

    private function getOffset(ConnectionInterface $conn)
    {
        parse_str($conn->httpRequest->getUri()->getQuery(), $arr);
        return (isset($arr['offset'])) ? invertSign(((int)$arr['offset'])*60) : 0;
    }

    private function getPath(ConnectionInterface $conn)
    {
        parse_str($conn->httpRequest->getUri()->getQuery(), $arr);
        return isset($arr['path']) ? $arr['path'] : false;
    }

    private function getSid(ConnectionInterface $conn)
    {
        $cookies = Cookies::fromRequest($conn->httpRequest);

        return $cookies->get('MOVIM_SESSION_ID')
            ? $cookies->get('MOVIM_SESSION_ID')->getValue()
            : null;
    }

    private function getHeaderSid(ConnectionInterface $conn)
    {
        return ($conn->httpRequest->hasHeader('MOVIM_SESSION_ID')
            && $conn->httpRequest->getHeader('MOVIM_DAEMON_KEY')[0] === $this->key)
            ? $conn->httpRequest->getHeader('MOVIM_SESSION_ID')[0]
            : null;
    }
}
