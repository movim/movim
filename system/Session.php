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
    protected static $instances = array();
    protected static $sid = null;
    protected $container;
    protected $max_age = 86400; // 24hours

    /**
     * Loads and immediately closes the session variables for the namespace
     * $name.
     */
    protected function __construct($name)
    {
        // Does the database exist?
        if(self::$sid == null) {
            if(isset($_COOKIE['PHPFASTSESSID'])) {
                self::$sid = $_COOKIE['PHPFASTSESSID'];
            } else {
                $this->regenerate();
            }
        }

        $this->container = $name;
    }

    protected function regenerate()
    {
        // Generating the session cookie's hash.
        $hash_chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $hash = "";

        for($i = 0; $i < 64; $i++) {
            $r = mt_rand(0, strlen($hash_chars) - 1);
            $hash.= $hash_chars[$r];
        }

        self::$sid = $hash;
        setcookie('PHPFASTSESSID', self::$sid, time() + $this->max_age);
    }

    /**
     * Gets a session handle.
     */
    public static function start($name)
    {
        if(!isset(self::$instances[$name])) {
            self::$instances[$name] = new self($name);
        }

        return self::$instances[$name];
    }

    /**
     * Gets a session variable. Returns false if doesn't exist.
     */
    public function get($varname)
    {
        $sd = new modl\SessionDAO();
        $data = $sd->get(self::$sid, $this->container, $varname);

        if($data) {
            return unserialize(base64_decode($data->value));
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
        
        $sd = new modl\SessionDAO();
        $sd->set(self::$sid, $this->container, $varname, $value);

        return $value;
    }

    /**
     * Deletes a variable from the session.
     */
    public function remove($varname)
    {
        $sd = new modl\SessionDAO();
        $sd->delete(self::$sid, $this->container, $varname);
    }
    
    /**
     * Deletes all variables of the session.
     */    
    public function delete_container()
    {
        $sd = new modl\SessionDAO();
        $sd->deleteContainer(self::$sid, $this->container);
    }

    /**
     * Deletes all this session container (not the session!)
     */
    public static function dispose($name)
    {
        if(isset(self::$instances[$name])) {
            self::$instances[$name]->delete_container();
            unset(self::$instances[$name]);
            return true;
        } else {
            return false;
        }
    }
    
    public static function clear()
    {
    
    }
}

?>
