<?php

namespace Movim\Daemon;

use Movim\Widget\Wrapper;
use Ratchet\Client\WebSocket;
use React\Dns\Resolver\ResolverInterface;

class LinkersManager
{
    private ResolverInterface $dns;
    private ?WebSocket $websocket = null;
    private array $linkers = [];

    public function __construct()
    {
        $config = \React\Dns\Config\Config::loadSystemConfigBlocking();
        $server = $config->nameservers ? reset($config->nameservers) : '8.8.8.8';

        $factory = new \React\Dns\Resolver\Factory();
        $this->dns = $factory->create($server);
    }

    public function linker(string $sid): ?Linker
    {
        return $this->linkers[$sid];
    }

    public function attachWebsocket(WebSocket $websocket)
    {
        $this->websocket = $websocket;
    }

    public function sendWebsocket(string $sid, \stdClass $message)
    {
        $message->sid = $sid;
        $this->websocket->send(json_encode($message));
    }

    public function closeLinker(string $sid)
    {
        unset($this->linkers[$sid]);
        logOut(colorize('Linker destroyed', 'red'), sid: $sid);

        $message = new \stdClass;
        $message->logout = true;
        $this->sendWebsocket($sid, $message);
    }

    public function handleMessage(\stdClass $message)
    {
        if ($message->func == 'new' && isset($message->sid)) {
            $this->linkers[$message->sid] = new Linker(
                linkersManager: $this,
                dns: $this->dns,
                sessionId: $message->sid,
                browserLocale: $message->browserLocale
            );
            logOut(colorize('Linker created', 'green'), sid: $message->sid);
            return;
        }

        if (!array_key_exists($message->sid, $this->linkers)) {
            return;
        }

        $linker = $this->linkers[$message->sid];

        switch ($message->func) {
            case 'message':
                $linker->handleJSON($message->b, $message->sid);
                break;

            case 'up':
            case 'down':
                if ($linker->connected()) {
                    Wrapper::getInstance()->iterate(
                        key: 'session_' . $message->func,
                        sessionId: $message->sid,
                        user: $linker->user
                    );
                }
                break;

            case 'crash':
                exit;
                break;

            case 'unregister':
                $linker->logout();
                break;

            case 'register':
                // Set the host, useful for the CN certificate check
                $session = linker($message->sid)->session;

                // If the host is already set, we already launched the registration process
                if ($session->get('host')) {
                    return;
                }

                $session->set('host', $message->host);
                $linker->register($message->host);
                break;
        }
    }
}
