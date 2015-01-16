<?php

/**
 * @file Session.php
 * This file is part of MOVIM.
 *
 * @brief Class that manages session variables with minimal lock time.
 *
 * @author Guillaume Pasquet <etenil@etenilsrealm.nl>
 *
 * Yes, this class is again a singleton. But this is justified by the fact that
 * there can only be one active session that is locked down.
 *
 * @version 1.0
 * @date 26 April 2011
 *
 * Copyright (C)2011 MOVIM
 *
 * See COPYING for licensing information.
 */

class Session
{
    //protected $db;
    protected static $instance;
    protected static $sid = null;
    protected $values = array();

    /**
     * Loads and immediately closes the session variables for the namespace
     * $name.
     */
    protected function __construct()
    {
    }

    /**
     * Gets a session handle.
     */
    public static function start($name = false)
    {
        if(!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Gets a session variable. Returns false if doesn't exist.
     */
    public function get($varname)
    {
        if(array_key_exists($varname, $this->values)) {
            return unserialize(base64_decode($this->values[$varname]));
        } else {
            return false;
        }
    }

    /**
     * Sets a session variable. Returns $value.
     */
    public function set($varname, $value)
    {
        $value = base64_encode(serialize($value));
        $this->values[$varname] = $value;

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
     * Deletes all this session container (not the session!)
     */
    public static function dispose()
    {
        if(isset(self::$instance)) {
            self::$instance = null;
            return true;
        } else {
            return false;
        }
    }
}

?>
