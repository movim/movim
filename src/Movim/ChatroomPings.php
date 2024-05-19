<?php
/*
 * SPDX-FileCopyrightText: 2024 Jaussoin Timothée
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Movim;

use Moxl\Xec\Action\Ping\Room;

/**
 * Handling XEP-0410: MUC Self-Ping (Schrödinger's Chat) pings and timeouts
 */
class ChatroomPings
{
    protected static $instance;
    private $_chatrooms = [];
    private $_timeout = 5 * 60;

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function touch(string $from)
    {
        global $loop;

        $this->clear($from);

        $this->_chatrooms[$from] = $loop->addTimer($this->_timeout, function () use ($from) {
            $presence = \App\User::me()->session->conferences()
                ->where('conference', $from)
                ->first()?->presence;

            if ($presence) {
                $pingRoom = new Room;
                $pingRoom->setResource($from . '/' . $presence->resource)
                         ->setRoom($from)
                         ->request();
            }
        });
    }

    public function clear(string $from)
    {
        global $loop;

        if (array_key_exists($from, $this->_chatrooms)) {
            $loop->cancelTimer($this->_chatrooms[$from]);
        }
    }
}