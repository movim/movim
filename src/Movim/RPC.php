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

    public function __construct(private ?User $user = null)
    {
    }

    public static function call($funcname, ...$args)
    {
        $payload = [
            'func' => $funcname
        ];

        if (!empty($args)) {
            $payload['p'] = $args;
        }

        if (php_sapi_name() != 'cli') {
            array_push(self::$json, $payload);
        } else {
            \writeOut($payload);
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
    public function handleJSON($request)
    {
        if (!isset($request->w)) {
            return;
        }

        $wrapper = new Wrapper;

        if ($this->user) {
            $wrapper = $wrapper->setUser($this->user);
        }

        $wrapper->runWidget(
            (string)$request->w,
            (string)$request->f,
            isset($request->p) ? (array)$request->p : []
        );
    }
}
