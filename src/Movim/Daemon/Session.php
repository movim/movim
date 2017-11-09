<?php
namespace Movim\Daemon;

use Ratchet\ConnectionInterface;
use React\EventLoop\Timer\Timer;
use Movim\Controller\Front;

class Session
{
    protected   $clients;
    public      $timestamp;
    protected   $sid;
    protected   $baseuri;
    public      $process;
    public      $pullSocket;
    public      $pushSocket;

    public      $registered;
    public      $started;

    private     $state;

    private     $verbose;
    private     $debug;

    private     $context;

    private     $language;
    private     $offset;

    public function __construct($loop, $sid, $context, $baseuri,
        $language = false, $offset = 0, $verbose = false, $debug = false)
    {
        $this->sid     = $sid;
        $this->baseuri = $baseuri;
        $this->language = $language;
        $this->offset = $offset;

        $this->verbose = $verbose;
        $this->debug = $debug;

        $this->clients = new \SplObjectStorage;
        $this->register($loop, $this, $context);

        $this->timestamp = time();
    }

    public function attach($loop, ConnectionInterface $conn)
    {
        $this->clients->attach($conn);

        if ($this->verbose) {
            echo colorize($this->sid, 'yellow'). " : ".colorize($conn->resourceId." connected\n", 'green');
        }

        if ($this->countClients() > 0) {
            $this->stateOut('up');
        }
    }

    public function detach($loop, ConnectionInterface $conn)
    {
        $this->clients->detach($conn);

        if ($this->verbose) {
            echo colorize($this->sid, 'yellow'). " : ".colorize($conn->resourceId." deconnected\n", 'red');
        }

        if ($this->countClients() == 0) {
            $loop->addPeriodicTimer(20, function($timer) {
                if ($this->countClients() == 0) {
                    $this->stateOut('down');
                }
                $timer->cancel();
            });
        }
    }

    public function countClients()
    {
        return $this->clients->count();
    }

    private function register($loop, $me, $context)
    {
echo 'begin ' . shell_exec('ls /proc/'.getmypid().'/fd | wc -l');
        $this->pullSocket = $context->getSocket(\ZMQ::SOCKET_PULL);
        $this->pushSocket = $context->getSocket(\ZMQ::SOCKET_PUSH);

        // Communication sockets with the linker
        $file = CACHE_PATH . 'movim_feeds_' . $this->sid . '.ipc';
echo 'pull ' . shell_exec('ls /proc/'.getmypid().'/fd | wc -l');
        //$this->pullSocket->getWrappedSocket()->setSockOpt(\ZMQ::SOCKOPT_LINGER, 0);
        $this->pullSocket->bind('ipc://' . $file . '_pull', true);
        var_dump(get_class($this->pullSocket));
echo 'push ' . shell_exec('ls /proc/'.getmypid().'/fd | wc -l');
        //$this->pushSocket->getWrappedSocket()->setSockOpt(\ZMQ::SOCKOPT_LINGER, 0);
        $this->pushSocket->bind('ipc://' . $file . '_push', true);
echo 'pushbind ' . shell_exec('ls /proc/'.getmypid().'/fd | wc -l');
        $this->pullSocket->on('message', function($msg) use ($me) {
            $me->messageOut($msg);
        });

        // Launching the linker
        $this->process = new \React\ChildProcess\Process(
                            'exec php linker.php ' . $this->sid,
                            null,
                            [
                                'sid'       => $this->sid,
                                'baseuri'   => $this->baseuri,
                                'language'  => $this->language,
                                'offset'    => $this->offset,
                                'verbose'   => $this->verbose,
                                'debug'     => $this->debug
                            ]
                        );
        $this->process->start($loop);
echo 'al ' . shell_exec('ls /proc/'.getmypid().'/fd | wc -l');
        // The linker died, we close properly the session
        $this->process->on('exit', function($output) use ($me, $file, $context) {
            if ($me->verbose) {
                echo colorize($this->sid, 'yellow'). " : ".colorize("linker killed \n", 'red');
            }
echo 'process end ' . shell_exec('ls /proc/'.getmypid().'/fd | wc -l');
            $me->process = null;
            $me->closeAll();
echo 'process clean ' . shell_exec('ls /proc/'.getmypid().'/fd | wc -l');
            $me->pullSocket->unbind('ipc://' . $file . '_pull');
echo 'pull unbind ' . shell_exec('ls /proc/'.getmypid().'/fd | wc -l');
            $me->pushSocket->unbind('ipc://' . $file . '_push');
echo 'push unbind ' . shell_exec('ls /proc/'.getmypid().'/fd | wc -l');
            $me->pullSocket->close();
echo 'pull close ' . shell_exec('ls /proc/'.getmypid().'/fd | wc -l');
            $me->pushSocket->close();
echo 'push close ' . shell_exec('ls /proc/'.getmypid().'/fd | wc -l');
            unset($me->pullSocket);
            unset($me->pushSocket);
            unset($context);
echo 'end ' . shell_exec('ls /proc/'.getmypid().'/fd | wc -l');

            (new \Modl\PresenceDAO)->clearPresence();
            (new \Modl\SessionxDAO)->delete($this->sid);
        });

        $self = $this;

        $this->process->stderr->on('data', function($output) use ($me, $self) {
            if (strpos($output, 'registered') !== false) {
                $self->registered = true;
            } elseif (strpos($output, 'started') !== false) {
                $self->started = true;
            } else {
                echo $output;
            }
        });
    }

    public function killLinker()
    {
        if (isset($this->process)) {
            $this->process->terminate();
            $this->process = null;
        }
    }

    public function closeAll()
    {
        foreach ($this->clients as $client) {
            $client->close();
        }
    }

    public function stateOut($state)
    {
        if ($this->state == $state) return;

        if (isset($this->process)) {
            $this->state = $state;
            $msg = new \stdClass;
            $msg->func = $this->state;
            $msg = json_encode($msg);
            $this->pushSocket->send($msg);
        }
    }

    public function messageIn($msg)
    {
        $this->timestamp = time();
        $this->pushSocket->send($msg);
        unset($msg);
    }

    public function messageOut($msg)
    {
        $this->timestamp = time();
        if(!empty($msg)) {
            foreach ($this->clients as $client) {
                $client->send($msg);
            }
        }
    }
}
