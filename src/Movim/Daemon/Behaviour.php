<?php
namespace Movim\Daemon;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Behaviour implements MessageComponentInterface {
    protected $sessions = array(); // Store the sessions
    //protected $process;
    protected $baseuri;

    public function __construct($baseuri) {
        echo "Movim daemon launched - Base URI : {$baseuri}\n";
        $this->baseuri = $baseuri;
    }

    public function onOpen(ConnectionInterface $conn) {
        $cookies = $conn->WebSocket->request->getCookies();

        if(array_key_exists('MOVIM_SESSION_ID', $cookies)) {
            $sid = $cookies['MOVIM_SESSION_ID'];
            $this->sessions[$sid][$conn->resourceId] = $conn;

            // If a linker doesn't exist for the current session
            if(!array_key_exists('linker', $this->sessions[$sid])) {
                $loop = \React\EventLoop\Factory::create();
                $this->sessions[$sid]['process'] = new \React\ChildProcess\Process(
                                        'php linker.php',
                                        null,
                                        array(
                                            'sid'       => $sid,
                                            'baseuri'   => $this->baseuri
                                        )
                                    );
                $this->sessions[$sid]['process']->start($loop);
            }
            
            echo "{$cookies['MOVIM_SESSION_ID']} : {$conn->resourceId} connected\n";
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

        $sid = $this->getSid($from);

        if(!isset($msg->func)) {
            return;
        }
        
        switch ($msg->func) {
            // The browser ask for a new session
            case 'unregister':            
                if(array_key_exists('linker', $this->sessions[$sid])) {
                    $this->sessions[$sid]['process']->terminate();
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
                    if($from !== $client && $key != 'process') {
                        $client->send(json_encode($obj));
                    }
                }

                $session_size = count($this->sessions[$from->sid]);
                echo "{$from->sid} : {$from->resourceId} linker registered - session size {$session_size}\n";
                break;

            // A message is received !
            case 'message':            
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
                        if($from !== $client && $key != 'process') {
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
            $sid = $this->getSid($conn);

            if(array_key_exists($sid, $this->sessions)) {
                $session_size = count($this->sessions[$sid]);
                
                // The connection is closed, remove it, as we can no longer send it messages
                if(array_key_exists('linker', $this->sessions[$sid])
                && $conn->resourceId == $this->sessions[$sid]['linker']->resourceId) {
                    $obj = new \StdClass;
                    $obj->func = 'disconnected';

                    foreach($this->sessions[$conn->sid] as $key => $client) {
                        if($key != 'process') {
                            $client->send(json_encode($obj));
                            echo "{$client->resourceId} disconnected to login\n";
                        }
                    }
                    echo "{$conn->resourceId} linker disconnected - session size {$session_size}\n";
                    unset($this->sessions[$sid]);
                } else {
                    echo "{$conn->resourceId} disconnected - session size {$session_size}\n";
                    unset($this->sessions[$sid][$conn->resourceId]);
                }
            }
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }

    private function getSid(ConnectionInterface $conn) {
        $cookies = $conn->WebSocket->request->getCookies();

        if(array_key_exists('MOVIM_SESSION_ID', $cookies)) {
            $sid = $cookies['MOVIM_SESSION_ID'];
        } elseif(isset($conn->sid)) {
            $sid = $conn->sid;
        } else {
            $sid = null;
        }

        return $sid;
    }
}
