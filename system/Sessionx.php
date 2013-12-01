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
    protected static $_sid = null;
    protected static $_instance;
    private         $_max_age = 86400; // 24hour
    private         $_timestamp;
    
    private         $_rid;
    private         $_id;

    public          $user;
    public          $ressource;
    public          $sessionid;
    public          $url;
    public          $port;
    public          $host;
    public          $domain;
    public          $active = false;
    public          $config;
    /*
     * Session generation and handling part
     */

    protected function __construct()
    {
        // Does the database exist?
        if(self::$_sid == null) {
            if(isset($_COOKIE['MOVIM_SESSION_ID'])) {
                self::$_sid = $_COOKIE['MOVIM_SESSION_ID'];
            } else {
                $this->regenerate();
            }
        }
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

        self::$_sid = $hash;
        setcookie('MOVIM_SESSION_ID', self::$_sid, time() + $this->_max_age);
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
        $s->session     = self::$_sid;
        $s->user        = $this->user;
        $s->ressource   = $this->ressource;
        $s->rid         = $this->_rid;
        $s->sid         = $this->sessionid;
        $s->id          = $this->_id;
        $s->url         = $this->url;
        $s->port        = $this->port;    
        $s->host        = $this->host;    
        $s->domain      = $this->domain;  
        $s->config      = $this->config;  
        $s->active      = $this->active;  
        $s->timestamp   = $this->_timestamp;
        return $s;
    }
     
    public function init() {
        $this->_rid = 0;
        $this->_id  = 0;
        $sd = new modl\SessionxDAO();
        $s = $this->inject();
        $sd->init($s);
    }

    public function save() {

    }

    public function destroy() {
        $sd = new modl\SessionxDAO();
        $sd->delete(self::$_sid);
    }

    /*
     * rid and id specific getter, theses getter autoincrement each
     * time the value in the database
     */
    public function getId() {
        $sd = new modl\SessionxDAO();
        $this->_id = $sd->getId(self::$_sid);
        return $this->_id;
    }
     
    public function getRid() {
        $sd = new modl\SessionxDAO();
        $this->_rid = $sd->getRid(self::$_sid);
        return $this->_rid;
    }
}
