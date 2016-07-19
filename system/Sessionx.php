<?php

class Sessionx {
    protected static $_sessionid = null;
    protected static $_instance;
    private         $_max_age = 604800; // 24hour
    private         $_timestamp;

    private         $_user;
    private         $_password;
    private         $_resource;
    private         $_hash;
    private         $_host;
    private         $_active = false;
    private         $_config;
    private         $_start;

    /*
     * Session generation and handling part
     */
    protected function __construct()
    {
        if(isset($_COOKIE['MOVIM_SESSION_ID'])) {
            self::$_sessionid = $_COOKIE['MOVIM_SESSION_ID'];
        } elseif(SESSION_ID) {
            self::$_sessionid = SESSION_ID;
        } elseif(!headers_sent()) {
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
        $s->host        = $this->_host;
        $s->config      = serialize($this->_config);
        $s->active      = $this->_active;
        $s->start       = $this->_start;
        $s->timestamp   = $this->_timestamp;
        return $s;
    }

    public function init($user, $pass, $host) {
        $this->_host        = $host;
        $this->_user        = $user;
        $this->_password    = $pass;
        $this->_resource    = 'moxl'.\generateKey(6);
        $this->_start       = date(DATE_ISO8601);

        $sd = new \Modl\SessionxDAO();
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
            $this->_host        = $session->host;
            $this->_config      = unserialize($session->config);
            $this->_active      = $session->active;
            $this->_start       = $session->start;
            $this->_timestamp   = $session->timestamp;
        }

        self::$_instance = $this;
    }

    public function __get($key) {
        if($key == 'sessionid') {
            return self::$_sessionid;
        } else {
            if(
                in_array(
                    $key,
                    array(
                        'host',
                        'user',
                        'password',
                        'hash',
                        'start')
                    )
            ) {
                $key = '_'.$key;
                return $this->$key;
            } else {
                $sd = new modl\SessionxDAO();
                $session = $sd->get(self::$_sessionid);
                if(isset($session->config))
                    $session->config = unserialize($session->config);

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

        $sd = new modl\SessionxDAO();
        $sd->update(self::$_sessionid, $key, $value);
    }

    public function destroy() {
        $sd = new \Modl\SessionxDAO();
        $sd->delete(self::$_sessionid);
    }
}
