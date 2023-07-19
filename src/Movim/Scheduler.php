<?php
/*
 * SPDX-FileCopyrightText: 2023 Jaussoin Timothée
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Movim;

/**
 * This class handle all the outgoing chatstates
 */
class Scheduler
{
    protected static $instance;
    private $_stack = [];

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function start()
    {
        global $loop;

        $loop->addPeriodicTimer(0.5, function () {
            if (count($this->_stack)) {
                \Utils::debug('Scheduler size '.count($this->_stack));
            }
            if (!empty($this->_stack)) {
                $key = key($this->_stack);
                \Utils::debug('EXEC '.$key);
                call_user_func($this->_stack[$key]);
                unset($this->_stack[$key]);
            }
        });
    }

    public function append(string $key, $function)
    {
        $this->_stack[$key] = $function;
    }
}
