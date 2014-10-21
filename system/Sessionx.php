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
    private         $_ressource;
    private         $_sid;
    private         $_url;
    private         $_port;
    private         $_host;
    private         $_domain;
    private         $_start;
    private         $_active = false;
    private         $_config;
    /*
     * Session generation and handling part
     */

    protected function __construct()
    {
        // Does the database exist?
        if(self::$_sessionid == null) {
            if(isset($_COOKIE['MOVIM_SESSION_ID'])) {
                self::$_sessionid = $_COOKIE['MOVIM_SESSION_ID'];
            } else {
                $this->regenerate();
            }
        }
    }

    protected function regenerate()
    {
        // Generating the session cookie's hash.
        self::$_sessionid = \generateKey(64);
        setcookie('MOVIM_SESSION_ID', self::$_sessionid, time() + $this->_max_age);
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
        $s->ressource   = $this->_ressource;
        $s->rid         = $this->_rid;
        $s->sid         = $this->_sid;
        $s->id          = $this->_id;
        $s->url         = $this->_url;
        $s->port        = $this->_port;    
        $s->host        = $this->_host;    
        $s->domain      = $this->_domain;  
        $s->config      = serialize($this->_config);  
        $s->active      = $this->_active;  
        $s->start       = $this->_start;
        $s->timestamp   = $this->_timestamp;
        return $s;
    }

    public function init($user, $pass, $host, $domain) {
        $cd = new \Modl\ConfigDAO();
        $config = $cd->get();
        
        $this->_url         = $config->boshurl;
        $this->_port        = 5222;
        $this->_host        = $host;
        $this->_domain      = $domain;
        $this->_user        = $user;
        $this->_password    = $pass;
        $this->_ressource   = 'moxl'.\generateKey(6);
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
            $this->_ressource   = $session->ressource;
            $this->_rid         = $session->rid;
            $this->_sid         = $session->sid;
            $this->_id          = $session->id;
            $this->_url         = $session->url;
            $this->_port        = $session->port;
            $this->_host        = $session->host;
            $this->_domain      = $session->domain;
            $this->_config      = unserialize($session->config);
            $this->_active      = $session->active;
            $this->_start       = $session->start;
            $this->_timestamp   = $session->timestamp;
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
                        'url',
                        'port',
                        'id',
                        'host',
                        'domain',
                        'user',
                        'config',
                        'password',
                        'start',
                        'ressource')
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
