<?php
namespace Movim\Daemon;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Behaviour implements MessageComponentInterface {
    protected $sessions = array(); // Store the sessions
    protected $process;
    protected $baseuri;

    public function __construct($baseuri) {
        echo "Movim daemon launched - Base URI : {$baseuri}\n";
        $this->baseuri = $baseuri;
    }

    public function onOpen(ConnectionInterface $conn) {
        $cookies = $conn->WebSocket->request->getCookies();

        if(array_key_exists('PHPSESSID', $cookies)) {
            $sid = $cookies['PHPSESSID'];
            $this->sessions[$sid][$conn->resourceId] = $conn;

            // If a linker doesn't exist for the current session
            if(!array_key_exists('linker', $this->sessions[$sid])) {
                $loop = \React\EventLoop\Factory::create();
                $this->process = new \React\ChildProcess\Process(
                                        'php linker.php',
                                        null,
                                        array(
                                            'sid'       => $sid,
                                            'baseuri'   => $this->baseuri
                                        )
                                    );
                $this->process->start($loop);
            }
            
            echo "{$cookies['PHPSESSID']} : {$conn->resourceId} connected\n";
        } else {
            //var_dump(get_class_methods($conn->WebSocket->request));
            //var_dump($conn->WebSocket->request->getBody());
            //var_dump($conn->WebSocket->request->getHeaders());
            //var_dump($conn->WebSocket->request->getParams());
            //var_dump($conn->WebSocket->request->getCookies());
            //var_dump($conn->WebSocket->request->getUrl());
            //var_dump($conn->WebSocket->request->getState());
            //var_dump($conn->WebSocket->request->getHeaderLines());
        }
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $msg = json_decode($msg);

        if(!isset($msg->func)) {
            return;
        }
        
        switch ($msg->func) {
            // The browser ask for a new session
            case 'ask':
                $id = $this->generateId();

                $obj = new \StdClass;
                $obj->id = $id;
                $from->send(json_encode($obj));
                break;

            case 'unregister':
                $cookies = $from->WebSocket->request->getCookies();

                if(array_key_exists('PHPSESSID', $cookies)) {
                    $sid = $cookies['PHPSESSID'];
                } else {
                    $sid = $from->sid;
                }
            
                if(array_key_exists('linker', $this->sessions[$sid])) {
                    $this->process->terminate();
                }
                break;

            // A linker ask to be linked to a session
            case 'register_linker':
                if(array_key_exists($msg->sid, $this->sessions) &&
                !array_key_exists('linker', $this->sessions[$msg->sid])) {
                    $from->sid = $msg->sid;
                    $this->sessions[$from->sid]['linker'] = $from;
                }

                $obj = new \StdClass;
                $obj->func = 'registered';

                foreach($this->sessions[$from->sid] as $key => $client) {
                    if($from !== $client) {
                        $client->send(json_encode($obj));
                    }
                }

                $session_size = count($this->sessions[$from->sid]);
                echo "{$from->sid} : {$from->resourceId} linker registered - session size {$session_size}\n";
                break;

            // A message is received !
            case 'message':
                $cookies = $from->WebSocket->request->getCookies();

                if(array_key_exists('PHPSESSID', $cookies)) {
                    $sid = $cookies['PHPSESSID'];
                } else {
                    $sid = $from->sid;
                }
            
                // Forbid any incoming messages if the session is not linked to XMPP
                if(!array_key_exists('linker', $this->sessions[$sid])) {
                    //$from->send(json_encode('linker not connected'));
                    return;
                }

                $msg->body = (string)json_encode($msg->body);
            
                // A message from the linker to the clients
                if($from === $this->sessions[$sid]['linker']) {
                    //echo "{$from->sid} : {$msg->body} got from the linker\n";
                    foreach($this->sessions[$sid] as $key => $client) {
                        if($from !== $client) {
                            //The sender is not the receiver, send to each client connected
                            if(isset($msg->body)) {
                                $client->send($msg->body);
                            }
                        }
                    }
                // A message from the browser to the linker
                } else {
                    //echo "{$from->sid} : {$msg->body} sent to the linker\n";
                    $this->sessions[$sid]['linker']->send((string)$msg->body);
                }
                break;
            default:
                $from->send('no function specified');
                break;
        }
    }

    public function onClose(ConnectionInterface $conn) {
        if(count($this->sessions) > 0) {
            $cookies = $conn->WebSocket->request->getCookies();

            if(array_key_exists('PHPSESSID', $cookies)) {
                $sid = $cookies['PHPSESSID'];
            } else {
                $sid = $conn->sid;
            }
            
            $session_size = count($this->sessions[$sid]);
            
            // The connection is closed, remove it, as we can no longer send it messages
            if(array_key_exists('linker', $this->sessions[$sid])
            && $conn->resourceId == $this->sessions[$sid]['linker']->resourceId) {
                $obj = new \StdClass;
                $obj->func = 'disconnected';

                foreach($this->sessions[$conn->sid] as $key => $client) {
                    $client->send(json_encode($obj));
                    echo "{$client->resourceId} disconnected to login\n";
                }
                echo "{$conn->resourceId} linker disconnected - session size {$session_size}\n";
                unset($this->sessions[$sid]);
            } else {
                echo "{$conn->resourceId} disconnected - session size {$session_size}\n";
                unset($this->sessions[$sid][$conn->resourceId]);
            }
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }

    private function generateId() {
        // Generating the session cookie's hash.
        $hash_chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $hash = "";

        for($i = 0; $i < 16; $i++) {
            $r = mt_rand(0, strlen($hash_chars) - 1);
            $hash.= $hash_chars[$r];
        }

        return $hash;
    }
}
