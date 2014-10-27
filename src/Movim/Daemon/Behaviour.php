<?php
namespace Movim\Daemon;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Behaviour implements MessageComponentInterface {
    protected $sessions = array(); // Store the sessions

    public function __construct() {
        echo "Movim daemon launched\n";
    }

    public function onOpen(ConnectionInterface $conn) {
        echo "{$conn->resourceId} connected\n";
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

            // A browser websocket ask to be linked to an existent session
            case 'register':
                $from->sid = $msg->sid;

                if(!array_key_exists($from->sid, $this->sessions)) {
                    $this->sessions[$from->sid] = array();
                }
                
                $this->sessions[$from->sid][$from->resourceId] = $from;

                // If a linker doesn't exist for the current session
                if(!array_key_exists('linker', $this->sessions[$from->sid])) {
                    $from->send(json_encode('session linked'));
                    
                    $loop = \React\EventLoop\Factory::create();
                    $process = new \React\ChildProcess\Process('php linker.php', null, array('sid' => $from->sid));
                    $process->start($loop);
                }

                $session_size = count($this->sessions[$from->sid]);
                echo "{$from->sid} : {$from->resourceId} registered - session size {$session_size}\n";
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
                // Forbid any incoming messages if the session is not linked to XMPP
                if(!array_key_exists('linker', $this->sessions[$from->sid])) {
                    $from->send(json_encode('linker not connected'));
                    return;
                }

                $msg->body = (string)json_encode($msg->body);
            
                // A message from the linker to the clients
                if($from === $this->sessions[$from->sid]['linker']) {
                    //echo "{$from->sid} : {$msg->body} got from the linker\n";
                    foreach($this->sessions[$from->sid] as $key => $client) {
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
                    $this->sessions[$from->sid]['linker']->send((string)$msg->body);
                }
                break;
            default:
                $from->send('no function specified');
                break;
        }
    }

    public function onClose(ConnectionInterface $conn) {
        if(count($this->sessions) > 0) {
            $session_size = count($this->sessions[$conn->sid]);
            
            // The connection is closed, remove it, as we can no longer send it messages
            if(array_key_exists('linker', $this->sessions[$conn->sid])
            && $conn->resourceId == $this->sessions[$conn->sid]['linker']->resourceId) {
                //echo serialize(array_keys($this->sessions[$conn->sid]));
                $obj = new \StdClass;
                $obj->func = 'disconnected';

                foreach($this->sessions[$conn->sid] as $key => $client) {
                    $client->send(json_encode($obj));
                    echo "{$client->resourceId} disconnected to login\n";
                }
                echo "{$conn->resourceId} linker disconnected - session size {$session_size}\n";
                unset($this->sessions[$conn->sid]);
            } else {
                echo "{$conn->resourceId} disconnected - session size {$session_size}\n";
                unset($this->sessions[$conn->sid][$conn->resourceId]);
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
