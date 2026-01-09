<?php
/*
 * SPDX-FileCopyrightText: 2010 Jaussoin Timothée
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Movim\Daemon\Linker;

use App\User;

use Moxl\Xec\Action\Message\Composing;
use Moxl\Xec\Action\Message\Paused;

/**
 * This class handle all the outgoing chatstates
 */
class ChatOwnState
{
    private $_to = null;
    private $_muc = false;
    private $_timer;
    private $_timeout = 5;

    public function __construct(private User $user)
    {
    }

    public function composing(string $to, bool $muc = false)
    {
        global $loop;

        if ($this->_to !== $to) {
            $mc = new Composing($this->user, sessionId: $this->user->session->id);

            if ($muc) {
                $mc->setMuc();
            }

            $mc->setTo($to)->request();

            if ($this->_to !== null) {
                $this->paused($this->_to, $this->_muc);
            }

            $this->_to = $to;
            $this->_muc = $muc;
        }

        if ($this->_timer) {
            $loop->cancelTimer($this->_timer);
        }

        $this->_timer = $loop->addTimer($this->_timeout, function () use ($to, $muc) {
            $this->paused($to, $muc);
        });
    }

    public function paused(string $to, bool $muc = false)
    {
        $this->halt();

        $mp = new Paused($this->user, sessionId: $this->user->session->id);

        if ($muc) {
            $mp->setMuc();
        }

        $mp->setTo($to)->request();
    }

    public function halt()
    {
        global $loop;

        $this->_to = null;
        $this->_muc = false;

        if ($this->_timer) {
            $loop->cancelTimer($this->_timer);
        }
    }
}
