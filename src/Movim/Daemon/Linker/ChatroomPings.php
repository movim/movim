<?php
/*
 * SPDX-FileCopyrightText: 2024 Jaussoin Timothée
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Movim\Daemon\Linker;

use App\User;
use Moxl\Xec\Action\Ping\Room;
use App\Widgets\Rooms\Rooms as WidgetRooms;

/**
 * Handling XEP-0410: MUC Self-Ping (Schrödinger's Chat) pings and timeouts
 */
class ChatroomPings
{
    private $_chatrooms = [];
    private $_chatroomsTimeout = [];
    private $_pingIn = 5 * 60;
    private $_pongTimeout = 5 * 60 + 120;

    public function __construct(private ?User $user = null)
    {
    }

    public function has(string $from): bool
    {
        return array_key_exists($from, $this->_chatrooms);
    }

    public function touch(string $from)
    {
        global $loop;

        $this->clear($from);

        $this->_chatrooms[$from] = $loop->addTimer($this->_pingIn, function () use ($from) {
            $presence = $this->user->session->conferences()
                ->where('conference', $from)
                ->first()?->presence;

            if ($presence) {
                $pingRoom = new Room($this->user, sessionId: $this->user->session->id);
                $pingRoom->setResource($from . '/' . $presence->resource)
                         ->setRoom($from)
                         ->request();
            }
        });

        $this->_chatroomsTimeout[$from] = $loop->addTimer($this->_pongTimeout, function () use ($from) {
            (new WidgetRooms($this->user, sessionId: $this->user->session->id))->ajaxExit($from);
        });
    }

    public function clear(string $from)
    {
        global $loop;

        if (array_key_exists($from, $this->_chatrooms)) {
            $loop->cancelTimer($this->_chatrooms[$from]);
            $loop->cancelTimer($this->_chatroomsTimeout[$from]);
        }
    }
}