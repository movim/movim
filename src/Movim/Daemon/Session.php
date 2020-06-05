<?php
namespace Movim\Daemon;

use Ratchet\ConnectionInterface;
use App\Session as DBSession;

class Session
{
    const DOWN_TIMER = 20;
    protected $clients;       // Browser Websockets
    public $timestamp;
    protected $sid;           // Session id
    protected $baseuri;
    public $process;       // Linker
    public $internalSocket;// Linker to Session Websocket

    private $port;         // Daemon Websocket port
    private $key;          // Daemon secure key

    public $registered;
    public $started;

    private $state;

    private $verbose;
    private $debug;

    private $language;
    private $offset;

    public function __construct(
        $loop,
        $sid,
        $baseuri,
        $port,
        $key,
        $language = false,
        $offset = 0,
        $verbose = false,
        $debug = false
    ) {
        $this->sid     = $sid;
        $this->baseuri = $baseuri;
        $this->language = $language;
        $this->offset = $offset;

        $this->port = $port;
        $this->key = $key;

        $this->verbose = $verbose;
        $this->debug = $debug;

        $this->clients = new \SplObjectStorage;
        $this->register($loop);

        $this->timestamp = time();
    }

    public function attach(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);

        if ($this->verbose) {
            echo colorize($this->sid, 'yellow'). " : ".colorize($conn->resourceId." connected\n", 'green');
        }

        if ($this->countClients() > 0) {
            $this->stateOut('up');
        }
    }

    public function attachInternal(ConnectionInterface $conn)
    {
        $this->internalSocket = $conn;

        if ($this->verbose) {
            echo colorize($this->sid, 'yellow'). " : ".colorize($conn->resourceId." internal connected\n", 'green');
        }
    }

    public function detach($loop, ConnectionInterface $conn)
    {
        $this->clients->detach($conn);

        if ($this->verbose) {
            echo colorize($this->sid, 'yellow'). " : ".colorize($conn->resourceId." deconnected\n", 'red');
        }

        if ($this->countClients() == 0) {
            $loop->addPeriodicTimer(Session::DOWN_TIMER, function ($timer) use ($loop) {
                if ($this->countClients() == 0) {
                    $this->stateOut('down');
                }
                $loop->cancelTimer($timer);
            });
        }
    }

    public function countClients()
    {
        return $this->clients->count();
    }

    private function register($loop)
    {
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
                'debug'     => $this->debug,
                'key'       => $this->key,
                'port'      => $this->port
            ]
        );
        $this->process->start($loop);

        // The linker died, we close properly the session
        $this->process->on('exit', function ($output) {
            if ($this->verbose) {
                echo colorize($this->sid, 'yellow'). " : ".colorize("linker killed \n", 'red');
            }

            $this->process = null;
            $this->closeAll();

            $session = DBSession::find($this->sid);
            if ($session) {
                $session->delete();
            }
        });

        $self = $this;

        $this->process->stderr->on('data', function ($output) use ($self) {
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
        if ($this->state == $state) {
            return;
        }

        if (isset($this->process)) {
            $this->state = $state;

            if ($this->internalSocket) {
                $msg = new \stdClass;
                $msg->func = $this->state;
                $msg = json_encode($msg);
                $this->internalSocket->send($msg);
            }
        }
    }

    public function messageIn($msg)
    {
        $this->timestamp = time();
        if ($this->internalSocket) {
            $this->internalSocket->send($msg);
        }
        unset($msg);
    }

    public function messageOut($msg)
    {
        $this->timestamp = time();
        if (!empty($msg)) {
            foreach ($this->clients as $client) {
                $client->send($msg);
            }
        }
    }
}
