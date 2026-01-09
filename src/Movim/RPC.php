<?php
/*
 * SPDX-FileCopyrightText: 2010 Jaussoin Timothée
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Movim;

use App\User;
use Movim\Widget\Wrapper;

class RPC
{
    private static $json = [];

    public function __construct(private ?User $user = null, private ?string $sessionId = null) {}

    public function call($funcname, ...$args)
    {
        $payload = new \stdClass;
        $payload->func = $funcname;

        if (!empty($args)) {
            $payload->p = $args;
        }

        if (php_sapi_name() != 'cli') {
            array_push(self::$json, $payload);
        } else if ($this->sessionId || $this?->user?->session) {
            global $linkerManager;
            $linkerManager->sendWebsocket(
                $this->sessionId ?? $this->user->session->id,
                $payload
            );
        }
    }

    public function writeJSON()
    {
        echo json_encode(self::$json);
        self::$json = [];
    }

    /**
     * Handles incoming requests.
     */
    public function handleJSON(\stdClass $request, ?string $sid = null)
    {
        if (!isset($request->w)) {
            return;
        }

        $wrapper = new Wrapper;
        $wrapper->runWidget(
            widgetName: (string)$request->w,
            method: (string)$request->f,
            params: isset($request->p) ? (array)$request->p : [],
            user: $this->user,
            sessionId: $sid
        );
    }
}
