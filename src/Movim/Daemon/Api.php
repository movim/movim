<?php

namespace Movim\Daemon;

use Movim\Bootstrap;

use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\LoopInterface;

use React\Http\HttpServer;
use React\Http\Message\Response;
use React\Socket\SocketServer;

class Api
{
    private $_core;

    public function __construct(SocketServer $socket, Core $core)
    {
        $this->_core = &$core;
        $api = &$this;

        $handler = function (ServerRequestInterface $request) use ($api) {
            $response = '';

            switch ($request->getUri()->getHost()) {
                case 'ajax':
                    $api->handleAjax($request->getParsedBody());
                    break;
                case 'exists':
                    $response = $api->sessionExists($request->getParsedBody());
                    break;
                case 'linked':
                    $response = $api->sessionsLinked();
                    break;
                case 'started':
                    $response = $api->sessionsStarted();
                    break;
                case 'unregister':
                    $response = $api->sessionUnregister($request->getParsedBody());
                    break;
                case 'disconnect':
                    $response = $api->sessionDisconnect($request->getParsedBody());
                    break;
                case 'session':
                    $response = $api->getSession();
                    break;
            }

            return new Response(
                200,
                ['Content-Type' => 'text/plain'],
                (string)$response
            );
        };

        $server = new HttpServer($handler);
        $server->on('error', function (\Throwable $e) {
            (new Bootstrap)->exceptionHandler($e);
        });
        $server->listen($socket);
    }

    public function handleAjax($post)
    {
        $sid = $post['sid'];
        if (array_key_exists($sid, $this->_core->sessions)) {
            $this->_core->sessions[$sid]->messageIn(rawurldecode($post['json']));
        }
    }

    public function sessionExists($post)
    {
        $sid = $post['sid'];

        $sessions = $this->_core->getSessions();

        return (array_key_exists($sid, $sessions)
        && $sessions[$sid] == true);
    }

    public function sessionsLinked()
    {
        return count($this->_core->getSessions());
    }

    public function getSession()
    {
        return count($this->_core->getSessions());
    }

    public function sessionsStarted()
    {
        $started = 0;
        foreach ($this->_core->getSessions() as $s) {
            if ($s == true) {
                $started++;
            }
        }
        return $started;
    }

    public function sessionUnregister($post)
    {
        $sid = $post['sid'];

        $session = $this->_core->getSession($sid);
        if ($session) {
            $session->messageIn(json_encode(['func' => 'unregister']));
        }
    }

    public function sessionDisconnect($post)
    {
        $sid = $post['sid'];

        return $this->_core->forceClose($sid);
    }
}
