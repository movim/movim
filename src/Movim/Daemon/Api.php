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
    private Core $core;

    public function __construct(SocketServer $socket, Core $core)
    {
        $this->core = $core;

        $handler = function (ServerRequestInterface $request): Response {
            $response = '';
            $post = (array) ($request->getParsedBody() ?? []);
            $response = match ($request->getUri()->getHost()) {
                'ajax'         => $this->handleAjax($post) ?? '',
                'exists'       => (string) $this->sessionExists($post),
                'linked'       => (string) count($this->core->getStartedSessions()),
                'started'      => (string) $this->sessionsStarted(),
                'mujiincall'   => (string) $this->isMujiInCall($post),
                'disconnect',
                'unregister'   => $this->sessionUnregister($post) ?? 'Unregistered',
                'sessionstree' => $this->core->dumpSessionsTree(),
            };

            return new Response(
                200,
                ['Content-Type' => 'text/plain'],
                (string) $response
            );
        };

        $server = new HttpServer($handler);
        $server->on('error', fn(\Throwable $e) => (new Bootstrap)->exceptionHandler($e));
        $server->listen($socket);
    }

    public function handleAjax(array $post): void
    {
        if (!isset($post['sid'], $post['json'])) {
            return;
        }

        if ($session = $this->core->findSession($post['sid'])) {
            $session->messageIn(rawurldecode($post['json']));
        }
    }

    public function sessionExists(array $post): bool
    {
        if (!isset($post['sid'])) {
            return false;
        }

        $sessions = $this->core->getStartedSessions();

        return (array_key_exists($post['sid'], $sessions)
            && $sessions[$post['sid']] === true);
    }

    public function sessionsStarted(): int
    {
        return count(array_filter($this->core->getStartedSessions(), fn($s) => $s === true));
    }

    public function isMujiInCall(array $post): bool
    {
        if (!isset($post['sessionid'], $post['jid'], $post['mujiroom'])) {
            return false;
        }

        $linker = linker($post['sessionid']);

        if (!$linker || !$linker->currentCall) {
            return false;
        }

        return $linker->currentCall->isJidInCall($post['jid'])
            && $linker->currentCall->mujiRoom === $post['mujiroom'];
    }

    public function sessionUnregister(array $post): void
    {
        if (!isset($post['sid'])) {
            return;
        }

        if ($session = $this->core->findSession($post['sid'])) {
            $session->messageIn(json_encode(['func' => 'unregister']));
        }
    }
}
