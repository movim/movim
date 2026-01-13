<?php
/*
 * SPDX-FileCopyrightText: 2010 Jaussoin Timothée
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Movim\Daemon;

use Ratchet\ConnectionInterface;

class Session
{
    const DOWN_TIMER = 10;
    private \SplObjectStorage $clients; // Browser Websockets
    public int $timestamp;

    public bool $registered = false;
    public bool $started = false;

    private $state;

    public function __construct(
        private SessionsWorker $worker,
        public string $sid,
        protected string $baseuri,
        private string $language,
    ) {
        $this->clients = new \SplObjectStorage;
        $this->timestamp = time();
    }

    public function attach(ConnectionInterface $conn)
    {
        $this->clients->offsetSet($conn);

        if (config('daemon.verbose')) {
            echo colorize($this->sid, 'yellow') . ": " . colorize($conn->resourceId . " connected\n", 'green');
        }

        if ($this->countClients() > 0) {
            $this->stateOut(state: 'up');
        }
    }

    public function spawnLinker()
    {
        $message = new \stdClass;
        $message->func = 'new';
        $message->sid = $this->sid;
        $message->browserLocale = $this->language;
        $this->worker->internalSocket->send(json_encode($message));
    }

    public function detach($loop, ConnectionInterface $conn)
    {
        $this->clients->offsetUnset($conn);

        if (config('daemon.verbose')) {
            echo colorize($this->sid, 'yellow') . ": " . colorize($conn->resourceId . " deconnected\n", 'red');
        }

        if ($this->countClients() == 0) {
            $loop->addPeriodicTimer(Session::DOWN_TIMER, function ($timer) use ($loop) {
                if ($this->countClients() == 0) {
                    $this->stateOut(state: 'down');
                }
                $loop->cancelTimer($timer);
            });
        }
    }

    public function countClients()
    {
        return $this->clients->count();
    }

    public function close()
    {
        foreach ($this->clients as $client) {
            $client->close();
        }

        $this->worker->closeSession($this->sid);
    }

    public function stateOut(string $state)
    {
        if ($this->state == $state) {
            return;
        }

        if (isset($this->process)) {
            $this->state = $state;

            if ($this->worker->internalSocket) {
                $msg = new \stdClass;
                $msg->func = $this->state;
                $msg->sid = $this->sid;
                $this->worker->internalSocket->send(json_encode($msg));
            }
        }
    }

    public function messageIn(string $msg)
    {
        // Inject the session sid
        $json = json_decode($msg);
        $json->sid = $this->sid;

        $this->timestamp = time();
        if ($this->worker->internalSocket) {
            $this->worker->internalSocket->send(json_encode($json));
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
