<?php

/**
 * @file Sessionx.php
 * This file is part of Movim.
 *
 * @brief This class manage the Movim current Movim. It doesn't replace
 * the other Session class. This class is a singleton.
 *
 * @author Jaussoin Timothée
 *
 * @version 1.0
 * @date 1 December 2013
 *
 * Copyright (C)2013 Movim
 *
 * See COPYING for licensing information.
 */
class Sessionx {
    protected static $_sessionid = null;
    protected static $_instance;
    private         $_max_age = 86400; // 24hour
    private         $_timestamp;
    
    private         $_rid;
    private         $_id;

    private         $_currentid;

    private         $_user;
    private         $_password;
    private         $_resource;
    private         $_hash;
    private         $_sid;
    private         $_port;
    private         $_host;
    private         $_domain;
    private         $_start;
    private         $_active = false;
    private         $_config;
    private         $_mechanism;
    /*
     * Session generation and handling part
     */

    protected function __construct()
    {
        if(isset($_COOKIE['MOVIM_SESSION_ID'])) {
            self::$_sessionid = $_COOKIE['MOVIM_SESSION_ID'];
        } elseif(SESSION_ID) {
            self::$_sessionid = SESSION_ID;
        } else {
            $key = generateKey(32); 
            setcookie("MOVIM_SESSION_ID", $key, time()+$this->_max_age, '/', false, APP_SECURED);
            self::$_sessionid = $key;
        }
    }

    public function refreshCookie()
    {
        if(isset($_COOKIE['MOVIM_SESSION_ID'])) {
            setcookie("MOVIM_SESSION_ID", $_COOKIE['MOVIM_SESSION_ID'], time()+$this->_max_age, '/', false, APP_SECURED);
        }
    }

    public static function start()
    {
        if(!isset(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /*
     * Session management part
     */
    private function inject() {
        $s = new modl\Sessionx();
        $s->session     = self::$_sessionid;
        $s->username    = $this->_user;
        $s->password    = $this->_password;
        $s->hash        = sha1($this->_user.$this->password.$this->host);
        $s->resource    = $this->_resource;
        $s->rid         = $this->_rid;
        $s->sid         = $this->_sid;
        $s->id          = $this->_id;
        $s->port        = $this->_port;    
        $s->host        = $this->_host;    
        $s->domain      = $this->_domain;  
        $s->config      = serialize($this->_config);  
        $s->active      = $this->_active;  
        $s->start       = $this->_start;
        $s->timestamp   = $this->_timestamp;
        $s->mechanism   = $this->_mechanism;
        return $s;
    }

    public function init($user, $pass, $host, $domain) {
        $this->_port        = 5222;
        $this->_host        = $host;
        $this->_domain      = $domain;
        $this->_user        = $user;
        $this->_password    = $pass;
        $this->_resource    = 'moxl'.\generateKey(6);
        $this->_start       = date(DATE_ISO8601);
        
        $this->_rid = rand(1, 2048);
        $this->_id  = 0;
        
        $sd = new modl\SessionxDAO();
        $s = $this->inject();
        $sd->init($s);
    }

    public function load() {
        $sd = new modl\SessionxDAO();
        $session = $sd->get(self::$_sessionid);
        
        if(isset($session)) {
            $this->_user        = $session->username;
            $this->_password    = $session->password;
            $this->_hash        = $session->hash;
            $this->_resource    = $session->resource;
            $this->_rid         = $session->rid;
            $this->_sid         = $session->sid;
            $this->_id          = $session->id;
            $this->_port        = $session->port;
            $this->_host        = $session->host;
            $this->_domain      = $session->domain;
            $this->_config      = unserialize($session->config);
            $this->_active      = $session->active;
            $this->_start       = $session->start;
            $this->_timestamp   = $session->timestamp;
            $this->_mechanism   = $session->mechanism;
        }

        self::$_instance = $this;
    }

    public function __get($key) {
        if($key == 'rid') {
            $sd = new modl\SessionxDAO();
            $this->_rid = $sd->getRid(self::$_sessionid);
            return $this->_rid;
        } else {
            if(
                in_array(
                    $key,
                    array(
                        'port',
                        'id',
                        'host',
                        'domain',
                        'user',
                        'password',
                        'hash',
                        'start',
                        'mechanism')
                    )
            ) {
                $key = '_'.$key;
                return $this->$key;
            } else {
                $sd = new modl\SessionxDAO();
                $session = $sd->get(self::$_sessionid);
                if(isset($session->config))
                    $session->config = unserialize($session->config);

                if($key == 'currentid')
                    $key = 'id';

                if(isset($session))
                    return $session->$key;
                else
                    return null;
            }
        }
    }

    public function __set($key, $value) {
        if($key == 'config')
            $value = serialize($value);
        elseif($key == 'user')
            $key = 'username';

        if($key == 'id') {
            $this->_id = $value;
            self::$_instance = $this;
        } else {
            $sd = new modl\SessionxDAO();
            $sd->update(self::$_sessionid, $key, $value);
        }
    }

    public function destroy() {
        $sd = new modl\SessionxDAO();
        $sd->delete(self::$_sessionid);
    }
}
