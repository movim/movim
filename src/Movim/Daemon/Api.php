<?php

namespace Movim\Daemon;

use Psr\Http\Message\ServerRequestInterface;
use React\Socket\Server as Reactor;

use React\Http\Middleware\LimitConcurrentRequestsMiddleware;
use React\Http\Middleware\RequestBodyBufferMiddleware;
use React\Http\Middleware\RequestBodyParserMiddleware;
use React\Http\Server;
use React\Http\Response;

class Api
{
    private $_core;

    public function __construct(Reactor $socket, Core $core)
    {
        $this->_core = &$core;
        $api = &$this;

        $handler = function (ServerRequestInterface $request) use ($api) {
            $url = explode('/', $request->getUri()->getPath());

            $response = '';

            switch($url[1]) {
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
                case 'disconnect';
                    $response = $api->sessionDisconnect($request->getParsedBody());
                    break;
                case 'session':
                    $response = $api->getSession($request->getParsedBody());
                    break;
            }

            return new Response(
                200,
                ['Content-Type' => 'text/plain'],
                (string)$response
            );
        };

        (new Server($handler))->listen($socket);
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
        foreach($this->_core->getSessions() as $s) {
            if ($s == true) $started++;
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
