<?php
/*
 * SPDX-FileCopyrightText: 2010 Jaussoin Timothée
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Movim;

use Psr\SimpleCache\CacheInterface;

class Session implements CacheInterface
{
    protected static $instance;
    protected static $sid = null;
    protected $values = [];
    private $seconds = 60; // Amount of seconds where the removable values are kept

    /**
     * Gets a session handle.
     */
    public static function instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function get($key, $default = null)
    {
        if (\array_key_exists($key, $this->values)) {
            return $this->values[$key]->value;
        }

        return $default;
    }

    public function getMultiple($keys, $default = null)
    {
        $values = [];

        foreach ($keys as $key) {
            $values[$key] = $this->get($key);
        }

        return $values;
    }

    public function set($key, $value, $ttl = null): bool
    {
        $obj = new \StdClass;
        $obj->removable = $ttl != null;
        $obj->value     = $value;
        $obj->time      = time();

        $this->values[$key] = $obj;

        return true;
    }

    public function setMultiple($values, $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }

        return true;
    }

    public function delete($key): bool
    {
        unset($this->values[$key]);
        return true;
    }

    public function deleteMultiple($keys): bool
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }

        return true;
    }

    public function clear(): bool
    {
        $this->values = [];
        return true;
    }

    public function has($key): bool
    {
        return array_key_exists($key, $this->values);
    }

    /**
     * Clean the old removable values
     */
    public function clean()
    {
        $t = time();

        foreach ($this->values as $key => $object) {
            if ($object->removable
            && $object->time < (int)$t - $this->seconds) {
                unset($this->values[$key]);
            }
        }
    }

    /**
     * Deletes all this session container (not the session!)
     */
    public static function dispose()
    {
        if (isset(self::$instance)) {
            self::$instance = null;
            return true;
        }

        return false;
    }
}
