<?php

namespace Movim;

class Session
{
    protected static $instance;
    protected static $sid = null;
    protected $values = [];
    private $seconds = 60; // Amount of seconds where the removable values are kept

    /**
     * Gets a session handle.
     */
    public static function start()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Gets a session variable. Returns false if doesn't exist.
     */
    public function get(string $varname, $defaultValue = false)
    {
        if (\array_key_exists($varname, $this->values)) {
            return $this->values[$varname]->value;
        }

        return $defaultValue;
    }

    /**
     * Sets a session variable. Returns $value.
     */
    public function set(string $varname, $value, bool $removable = false)
    {
        $obj = new \StdClass;
        $obj->removable = $removable;
        $obj->value     = $value;
        $obj->time      = time();

        $this->values[$varname] = $obj;

        return $value;
    }

    /**
     * Deletes a variable from the session.
     */
    public function remove($varname)
    {
        unset($this->values[$varname]);
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
