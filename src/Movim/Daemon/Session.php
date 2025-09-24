<?php
/*
 * SPDX-FileCopyrightText: 2010 Jaussoin Timothée
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Movim\Daemon;

use Ratchet\ConnectionInterface;
use React\ChildProcess\Process;
use React\EventLoop\LoopInterface;

use App\Session as DBSession;

class Session
{
    const DOWN_TIMER = 20;
    protected \SplObjectStorage $clients; // Browser Websockets
    public int $timestamp;
    protected string $sid;
    protected string $baseuri;
    public ?Process $process;
    public ?ConnectionInterface $internalSocket = null;

    private string $key; // Daemon secure key

    public bool $registered = false;
    public bool $started = false;

    private $state;
    private $language;

    public function __construct(
        LoopInterface $loop,
        string $sid,
        string $baseuri,
        string $key,
        $language = false
    ) {
        $this->sid = $sid;
        $this->baseuri = $baseuri;
        $this->language = $language;

        $this->key = $key;

        $this->clients = new \SplObjectStorage;
        $this->register($loop);

        $this->timestamp = time();
    }

    public function attach(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);

        if (config('daemon.verbose')) {
            echo colorize($this->sid, 'yellow') . " : " . colorize($conn->resourceId . " connected\n", 'green');
        }

        if ($this->countClients() > 0) {
            $this->stateOut('up');
        }
    }

    public function attachInternal(ConnectionInterface $conn)
    {
        $this->internalSocket = $conn;

        if (config('daemon.verbose')) {
            echo colorize($this->sid, 'yellow') . " : " . colorize($conn->resourceId . " internal connected\n", 'green');
        }
    }

    public function detach($loop, ConnectionInterface $conn)
    {
        $this->clients->detach($conn);

        if (config('daemon.verbose')) {
            echo colorize($this->sid, 'yellow') . " : " . colorize($conn->resourceId . " deconnected\n", 'red');
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

    private function register(LoopInterface $loop)
    {
        // Only load the required extensions
        $configuration = '-n ';

        foreach (requiredExtensions() as $extension) {
            $configuration .= '-dextension=' . $extension . '.so ';
        }

        // Enable Opcache
        if (isOpcacheEnabled()) {
            $configuration .= '-dzend_extension=opcache.so -dopcache.enable=1 -dopcache.enable_cli=1 ';
        }

        // Launching the linker
        $this->process = new Process(
            'exec ' . PHP_BINARY . ' ' . $configuration . ' -d=memory_limit=512M linker.php ' . $this->sid,
            cwd: DOCUMENT_ROOT,
            env: [
                'baseuri'       => $this->baseuri,
                'DAEMON_DEBUG'  => config('daemon.debug'),
                'DAEMON_PORT'   => config('daemon.port'),
                'DAEMON_VERBOSE'=> config('daemon.verbose'),
                'DB_DATABASE'   => config('database.database'),
                'DB_DRIVER'     => config('database.driver'),
                'DB_HOST'       => config('database.host'),
                'DB_PASSWORD'   => config('database.password'),
                'DB_PORT'       => config('database.port'),
                'DB_USERNAME'   => config('database.username'),
                'key'           => $this->key,
                'language'      => $this->language,
                'sid'           => $this->sid,
            ]
        );
        $this->process->start($loop);

        // The linker died, we close properly the session
        $this->process->on('exit', function ($output) {
            if (config('daemon.verbose')) {
                echo colorize($this->sid, 'yellow') . " : " . colorize("linker killed \n", 'red');
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
