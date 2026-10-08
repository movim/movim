<?php
/*
 * SPDX-FileCopyrightText: 2026 Jaussoin Timothée
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace App\Workers\Galener;

use Psr\Http\Message\ServerRequestInterface;

use React\Http\HttpServer;
use React\Http\Message\Response;
use React\Socket\SocketServer;

class Api
{
    public function __construct(
        SocketServer $socket,
        private ConferencesManager $conferencesManager
    ) {
        $handler = function (ServerRequestInterface $request): Response {
            $response = '';
            $response = match ($request->getUri()->getHost()) {
                'conferences' => $this->conferencesStatus()
            };

            return new Response(
                200,
                ['Content-Type' => 'text/plain'],
                (string) $response
            );
        };

        $server = new HttpServer($handler);
        $server->on('error', fn(\Throwable $e) => \logError($e->getMessage()));
        $server->listen($socket);
    }

    private function conferencesStatus()
    {
        $conferences = [];

        foreach ($this->conferencesManager->conferences as $conference) {
            $conferences[$conference->getRoomJid()] = [
                'sfu_jid' => $conference->getSFUJid(),
                'started_at' => $conference->startedAt ? $conference->startedAt->format('d-m-Y - H:i:s') . ' GMT' : null,
                'connections' => []
            ];

            foreach ($conference->connections as $connection) {
                array_push($conferences[$conference->getRoomJid()]['connections'], (string)$connection->jid);
            }
        }

        return json_encode($conferences);
    }
}
