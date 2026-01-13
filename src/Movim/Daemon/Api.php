<?php
/*
 * SPDX-FileCopyrightText: 2010 Jaussoin Timothée
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Movim\Daemon;

use Movim\Bootstrap;

use Psr\Http\Message\ServerRequestInterface;

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
                    $response = count($this->_core->getStartedSessions());
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
                case 'sessionstree':
                    $response = $this->_core->dumpSessionsTree();
                    break;
            }

            return new Response(
                200,
                ['Content-Type' => 'text/plain'],
                (string)$response
            );
        };

        $server = new HttpServer($handler);
        $server->on('error', fn(\Throwable $e) => (new Bootstrap)->exceptionHandler($e));
        $server->listen($socket);
    }

    public function handleAjax($post)
    {
        if ($session = $this->_core->findSession($post['sid'])) {
            $session->messageIn(rawurldecode($post['json']));
        }
    }

    public function sessionExists($post)
    {
        $sid = $post['sid'];

        $sessions = $this->_core->getStartedSessions();

        return (array_key_exists($sid, $sessions)
            && $sessions[$sid] == true);
    }

    public function sessionsStarted()
    {
        $started = 0;
        foreach ($this->_core->getStartedSessions() as $s) {
            if ($s == true) {
                $started++;
            }
        }
        return $started;
    }

    public function sessionUnregister($post)
    {
        if ($session = $this->_core->findSession($post['sid'])) {
            $session->messageIn(json_encode(['func' => 'unregister']));
        }
    }

    public function sessionDisconnect($post)
    {
        if (array_key_exists('sid', $post)) {
            $sid = $post['sid'];
            return $this->_core->forceClose($sid);
        }
    }
}
