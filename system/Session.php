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

if(!class_exists('Session')):

class SessionVar extends DatajarBase
{
    protected $name;
    protected $value;
    protected $session;
    protected $container;
    protected $timestamp;

    protected function type_init()
    {
        $this->name      = DatajarType::varchar(128);
        $this->value     = DatajarType::text();
        $this->session   = DatajarType::varchar(128);
        $this->container = DatajarType::varchar(128);
        $this->timestamp = DatajarType::int();
    }
}

class Session
{
    protected $db;
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
        /*if(defined('TEST_DB_CONN')) {
            $this->db = new DatajarEngineWrapper(TEST_DB_CONN);
        } else {
            $this->db = new DatajarEngineWrapper(Conf::getServerConfElement('storageConnection'));
        }
        
        var_dump($this->db);*/

        // Does the database exist?
        $var = new SessionVar();
        //$query = $var->query()->create($var);
        //$var->run_query($query);

        if(self::$sid == null) {
            if(isset($_COOKIE['PHPFASTSESSID'])) {
                self::$sid = $_COOKIE['PHPFASTSESSID'];
            } else {
                $this->regenerate();
            }
        }

        $this->container = $name;
        Logger::log(1, "Session: Starting session ".self::$sid);
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
        /*$data = new SessionVar();
        
        $query = $data->query()->select($data)->where(array(
                               'session' => self::$sid,
                               'container' => $this->container,
                               'name' => $varname));
        
                //$query = $var->query()->save($var);
        $data->run_query($query);*/
        
        $query = SessionVar::query()->select()
                                   ->where(array(
                                           'session' => self::$sid,
                                           'container' => $this->container,
                                           'name' => $varname));
        $data = SessionVar::run_query($query);

        if($data) {
            return unserialize(base64_decode($data[0]->value));
        } else {
            return false;
        }
    }

    /**
     * Sets a session variable. Returns $value.
     */
    public function set($varname, $value)
    {
        // Does the variable exist?
        $var = new SessionVar();

        /*$success = $var->load(array(
                                   'session' => self::$sid,
                                   'container' => $this->container,
                                   'name' => $varname));*/
                                   
        $query = SessionVar::query()->select()
                                   ->where(array(
                                           'session' => self::$sid,
                                           'container' => $this->container,
                                           'name' => $varname))
                                   ->limit(0, 1);
        $data = SessionVar::run_query($query);
                                   
        //var_dump($data);
        
                

        Logger::log(1, "Session: Setting variable $varname");

        if($data) {
            $var = $data[0];
        }
        //if(!$success) {
            $var->session = self::$sid;
            $var->container = $this->container;
            $var->name = $varname;
        //}
        
        $var->value = base64_encode(serialize($value));
        $var->timestamp = time();
        
        $var->run_query($var->query()->save($var));

        return $var->value;
    }

    /**
     * Deletes a variable from the session.
     */
    public function remove($varname)
    {
        $var = new SessionVar();
        $var->load(array(
                            'session' => self::$sid,
                            'container' => $this->container,
                            'name' => $varname));

        $query = $var->query()->delete($var);
        $var->run_query($query);
    }

    public function delete_container()
    {
        /*$vars = $this->db->select('SessionVar', array('container' => $this->container,
                                                      'session' => self::$sid));*/
                                                      
        $query = SessionVar::query()->select()
                           ->where(array(
                                   'session' => self::$sid,
                                   'container' => $this->container));
        $vars = SessionVar::run_query($query);
        
        foreach($vars as $var)
        {
            $query = SessionVar::query()->delete($var);
            SessionVar::run_query($query);
        }
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

endif;

?>
