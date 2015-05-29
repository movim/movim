<?php
namespace Movim\Daemon;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Movim\Daemon\Session;

class Core implements MessageComponentInterface {
    private $sessions = array();
    public $loop;
    public $baseuri;

    private $cleanerdelay = 60; // in minutes

    public function __construct($loop, $baseuri, $port)
    {
        echo colorize("Movim daemon launched\n", 'green');
        echo colorize("Base URI :", 'green')." {$baseuri}\n";
        $ws = $this->setWebsocket($baseuri, $port);
        //echo colorize("Public WebSocket URL :", 'green')." {$ws}\n";

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

            $this->sessions[$sid]->attach($conn);
        }
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $sid = $this->getSid($from);
        if($sid != null) {
            $this->sessions[$sid]->messageIn($from, $msg);
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $sid = $this->getSid($conn);
        if($sid != null) {
            $this->sessions[$sid]->detach($conn);
            $this->closeEmptySession($sid);
        }
    }

    private function registerCleaner()
    {
        $this->loop->addPeriodicTimer(5, function() {
            foreach($this->sessions as $sid => $session) {
                if(time()-$session->timestamp > $this->cleanerdelay*60) {
                    $session->killLinker();
                    $this->closeEmptySession($sid);
                }
            }
        });
    }

    private function closeEmptySession($sid)
    {
        // No WebSockets and no linker ? We close the whole session
        if($this->sessions[$sid]->countClients() == 0
        && $this->sessions[$sid]->process == null) {
            $sd = new \Modl\SessionxDAO();
            $sd->delete($sid);
            
            unset($this->sessions[$sid]);
        }
    }
    
    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";
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
