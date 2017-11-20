<?php
namespace Movim\Daemon;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Movim\Daemon\Session;
use Dflydev\FigCookies\Cookies;

use Symfony\Component\Console\Input\InputInterface;

class Core implements MessageComponentInterface
{
    public $sessions = [];
    private $input;

    public $loop;
    public $baseuri;

    public $context;

    public $single = ['visio'];
    public $singlelocks = [];

    public function __construct($loop, $baseuri, InputInterface $input)
    {
        $this->input = $input;

        $this->setWebsocket($baseuri, $this->input->getOption('port'));

        $this->loop    = $loop;
        $this->baseuri = $baseuri;

        $this->context = new \React\ZMQ\Context($loop, new \ZMQContext(2, false));

        (new \Modl\SessionxDAO)->clear();

        $this->cleanupIPCs();
        $this->registerCleaner();
    }

    public function setWebsocket($baseuri, $port)
    {
        $explode = parse_url($baseuri);

        echo
            "\n".
            "--- ".colorize("Server Configuration - Apache", 'purple')." ---".
            "\n";
        echo colorize("Enable the Secure WebSocket to WebSocket tunneling", 'yellow')."\n# a2enmod proxy_wstunnel \n";
        echo colorize("Add this in your configuration file (default-ssl.conf)", 'yellow')."\nProxyPass /ws/ ws://localhost:{$port}/\n";

        echo
            "\n".
            "--- ".colorize("Server Configuration - nginx", 'purple')." ---".
            "\n";
        echo colorize("Add this in your configuration file", 'yellow')."\n";
        echo "location /ws/ {
    proxy_pass http://localhost:{$port}/;
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

        $path = $explode['host'].$explode['path'];

        if ($explode['scheme'] == 'https') {
            $ws = 'wss://'.$path.'ws/';
            $secured = 'true';
            echo colorize("Encrypted ", 'green')."\n";
        } else {
            $ws = 'ws://'.$path.'ws/';
            $secured = 'false';
            echo colorize("Unencrypted ", 'red')."\n";
        }

        file_put_contents(CACHE_PATH.'websocket', $secured);

        return $ws;
    }

    public function onOpen(ConnectionInterface $conn)
    {
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
                    $this->context,
                    $this->baseuri,
                    $language,
                    $offset,
                    $this->input->getOption('verbose'),
                    $this->input->getOption('debug')
                );
            }

            $this->sessions[$sid]->attach($this->loop, $conn);
        }
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $sid = $this->getSid($from);
        if ($sid != null && isset($this->sessions[$sid])) {
            $this->sessions[$sid]->messageIn($msg);
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $sid = $this->getSid($conn);

        if ($sid != null && isset($this->sessions[$sid])) {
            $path = $this->getPath($conn);

            if (in_array($path, $this->single)) {
                if(array_key_exists($sid, $this->singlelocks)
                && array_key_exists($path, $this->singlelocks[$sid])) {
                    $this->singlelocks[$sid][$path]--;
                    if($this->singlelocks[$sid][$path] == 0) {
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
        $this->loop->addPeriodicTimer(5, function() {
            foreach($this->sessions as $sid => $session) {
                if ($session->countClients() == 0
                && $session->registered == null) {
                    $session->killLinker();
                }

                if ($session->process == null) {
                    unset($this->sessions[$sid]);
                }
            }

            $this->cleanupDBSessions();
        });
    }

    private function cleanupDBSessions()
    {
        (new \Modl\SessionxDAO)->deleteEmpty();
        (new \Modl\PresenceDAO)->cleanPresences();
    }

    private function cleanupIPCs()
    {
        foreach (glob('/tmp/movim_feeds_*') as $ipc) {
            unlink($ipc);
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";
    }

    public function getSessions()
    {
        return array_map(
            function($session) { return $session->started; },
            $this->sessions);
    }

    public function getSession($sid)
    {
        if(isset($this->sessions[$sid])) {
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
        return $arr['path'] ?? false;
    }

    private function getSid(ConnectionInterface $conn)
    {
        $cookies = Cookies::fromRequest($conn->httpRequest);

        if ($cookies->get('MOVIM_SESSION_ID')) {
            return $cookies->get('MOVIM_SESSION_ID')->getValue();
        } else {
            return null;
        }
    }
}
