<?php
namespace Movim\Daemon;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Movim\Daemon\Session;

class Core implements MessageComponentInterface {
    private $sessions = array();
    public $loop;
    public $baseuri;

    public function __construct($loop, $baseuri, $port)
    {
        $baseuri = rtrim($baseuri, '/') . '/';

        echo colorize("Movim daemon launched\n", 'green');
        echo colorize("Base URI :", 'green')." {$baseuri}\n";
        $this->setWebsocket($baseuri, $port);

        $this->loop    = $loop;
        $this->baseuri = $baseuri;

        $sd = new \Modl\SessionxDAO();
        $sd->clear();

        $this->registerCleaner();
    }

    public function setWebsocket($baseuri, $port)
    {
        $explode = parse_url($baseuri);

        echo
            "\n".
            "--- ".colorize("Server Configuration - Apache", 'purple')." ---".
            "\n";
        echo colorize("Enable the Secure WebSocket to WebSocket tunneling", 'yellow')."\n$ a2enmod proxy_wstunnel \n";
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
    proxy_read_timeout 86400s;
    proxy_send_timeout 86400s;
    proxy_redirect off;
}

";

        $path = $explode['host'].$explode['path'];

        if($explode['scheme'] == 'https') {
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
        if($sid != null) {
            if(!array_key_exists($sid, $this->sessions)) {
                $this->sessions[$sid] = new Session($this->loop, $sid, $this->baseuri);
            }

            $this->sessions[$sid]->attach($this->loop, $conn);
        }
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $sid = $this->getSid($from);
        if($sid != null && isset($this->sessions[$sid])) {
            $this->sessions[$sid]->messageIn($msg);
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $sid = $this->getSid($conn);
        if($sid != null && isset($this->sessions[$sid])) {
            $this->sessions[$sid]->detach($this->loop, $conn);

            if($this->sessions[$sid]->process == null) {
                unset($this->sessions[$sid]);
            }
        }
    }

    public function forceClose($sid)
    {
        if(array_key_exists($sid, $this->sessions)) {
            $this->sessions[$sid]->killLinker();
            unset($this->sessions[$sid]);
        }
    }

    private function registerCleaner()
    {
        $this->loop->addPeriodicTimer(5, function() {
            foreach($this->sessions as $sid => $session) {
                if($session->countClients() == 0
                && $session->registered == null) {
                    $session->killLinker();
                }

                if($session->process == null) {
                    unset($this->sessions[$sid]);
                }
            }

            $this->cleanupDBSessions();
        });
    }

    private function cleanupDBSessions()
    {
        $sd = new \Modl\SessionxDAO();
        $sd->deleteEmpty();
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";
    }

    public function getSessions()
    {
        return array_map(
            function($session) { return $session->registered; },
             $this->sessions);
    }

    public function getSession($sid)
    {
        if(isset($this->sessions[$sid])) {
            return $this->sessions[$sid];
        }
    }

    private function getSid(ConnectionInterface $conn)
    {
        $cookies = $conn->WebSocket->request->getCookies();
        if(array_key_exists('MOVIM_SESSION_ID', $cookies)) {
            return $cookies['MOVIM_SESSION_ID'];
        } else {
            return null;
        }
    }
}
