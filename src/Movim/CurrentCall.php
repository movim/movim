<?php
/*
 * SPDX-FileCopyrightText: 2024 Jaussoin Timothée
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Movim;

use Carbon\Carbon;
use Movim\Widget\Wrapper;

/**
 * This class handle the current Jitsi call
 */
class CurrentCall
{
    protected static $instance;
    public ?string $jid = null;
    public ?string $id = null;
    public ?string $mujiRoom = null;
    public ?Carbon $startTime = null;

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function start(string $jid, string $id, ?string $mujiRoom = null): bool
    {
        if ($this->isStarted()) return false;

        $this->jid = $jid;
        $this->id = $id;
        $this->mujiRoom = $mujiRoom;
        $this->startTime = Carbon::now();

        $wrapper = Wrapper::getInstance();
        $wrapper->iterate('currentcall_started', [$this->getBareJid(), $id]);

        return true;
    }

    public function stop(string $jid, string $id): bool
    {
        if ($this->jid != $jid || $this->id != $id) return false;

        $jid = $this->getBareJid();
        $id = $this->id;

        $this->jid = $this->id = $this->mujiRoom = $this->startTime = null;

        $wrapper = Wrapper::getInstance();
        $wrapper->iterate('currentcall_stopped', [$jid, $id]);

        return true;
    }

    public function hasId(string $id): bool
    {
        return $this->id == $id;
    }

    public function isJidInCall(string $jid): bool
    {
        return $jid == $this->getBareJid();
    }

    public function isStarted(): bool
    {
        return $this->jid != null && $this->id != null;
    }

    public function getBareJid(): ?string
    {
        if (!$this->isStarted()) return null;

        return \baseJid($this->jid);
    }
}
