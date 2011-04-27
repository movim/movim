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
    protected static $instances = array();
    protected $session;
    protected $sid;
    protected $s_name;
    protected $nosave = false;

    /**
     * Loads and immediately closes the session variables for the namespace
     * $name.
     */
    protected function __construct($name)
    {
        $this->s_name = $name;
        $this->load();
    }

    /**
     * Gets an instance of Session.
     */
    public static function start($name)
    {
        if(!isset(self::$instances[$name])) {
            self::$instances[$name] = new self($name);
        }

        return self::$instances[$name];
    }

    /**
     * Commits the session upon destruction.
     */
    public function __destruct()
    {
        if(!$this->nosave) {
            $this->commit();
        }
    }

    /**
     * Loads data from the session.
     */
    protected function load()
    {
//        session_start();
        $this->sid = session_id();
        $this->session = unserialize(base64_decode($_SESSION[$this->s_name]));
//        session_commit();
    }

    /**
     * Gets a session variable. Returns false if doesn't exist.
     */
    public function get($varname)
    {
        if(isset($this->session[$varname])) {
            return $this->session[$varname];
        } else {
            return false;
        }
    }

    /**
     * Sets a session variable. Returns $value.
     */
    public function set($varname, $value)
    {
        $this->session[$varname] = $value;
        return $value;
    }

    /**
     * Deletes a variable from the session.
     */
    public function remove($varname)
    {
        if(isset($this->session[$varname])) {
            unset($this->session[$varname]);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Instance alias of the destroy function.
     */
    public function dispose()
    {
        $this->nosave = true;
        self::destroy($this->s_name);
    }

    /**
     * Deletes the whole namespace.
     */
    public static function destroy($name)
    {
//        session_start();
        session_unset($name);
//        session_commit();

        unset(self::$instances[$name]);
    }

    /**
     * Forces write of session. Call this once you have written data that needs
     * sharing.
     */
    public function commit()
    {
//        session_start();
        $_SESSION[$this->s_name] = base64_encode(serialize($this->session));
//        session_commit();
    }

    /**
     * Cancels all changes on the session (dangerous).
     */
    public function rollback()
    {
        $this->load();
    }
}

?>
