<?php
namespace Movim\Daemon;

use Ratchet\ConnectionInterface;

class Session {
    protected $clients;
    protected $timestamp;
    protected $sid;
    protected $baseuri;
    public $process;

    protected $buffer;

    public function __construct($loop, $sid, $baseuri)
    {
        $this->sid     = $sid;
        $this->baseuri = $baseuri;
        
        $this->clients = new \SplObjectStorage;
        $this->register($loop, $this);
    }

    public function attach(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        echo colorize($this->sid, 'yellow'). " : ".colorize($conn->resourceId." connected\n", 'green');
    }
    
    public function detach(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        echo colorize($this->sid, 'yellow'). " : ".colorize($conn->resourceId." deconnected\n", 'red');
    }

    public function countClients()
    {
        return $this->clients->count();
    }
    
    private function register($loop, $me)
    {
        $buffer = '';

        // Launching the linker
        $this->process = new \React\ChildProcess\Process(
                                        'php linker.php',
                                        null,
                                        array(
                                            'sid'       => $this->sid,
                                            'baseuri'   => $this->baseuri
                                        )
                                    );

        $this->process->start($loop);

        // Buffering the incoming data and fire it once its complete      
        $this->process->stdout->on('data', function($output) use ($me, &$buffer) {
            if(substr($output, -3) == "END") {
                $out = $buffer . substr($output, 0, -3);
                $buffer = '';
                $me->messageOut($out);
            } else {
                $buffer .= $output;
            }
        });

        // The linker died, we close properly the session
        $this->process->on('exit', function($output) use ($me) {
            echo colorize($this->sid, 'yellow'). " : ".colorize("linker killed \n", 'red');
            $me->process = null;
            $me->closeAll();
        });

        // Debug only, if the linker output some errors
        $this->process->stderr->on('data', function($output) use ($me) {
            echo $output;
        });
    }

    public function closeAll()
    {
        foreach ($this->clients as $client) {
            $client->close();
        }
    }
    
    public function messageIn(ConnectionInterface $from, $msg)
    {
        $this->process->stdin->write($msg."\n");
    }

    public function messageOut($msg)
    {
        if(!empty($msg)) {
            foreach ($this->clients as $client) {
                $client->send($msg);
            }
        }
    }
}
