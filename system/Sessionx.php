<?php

use Modl\SQL;

class Sessionx
{
    protected static $_instance;
    private         $_max_age = 604800; // 24hour
    private         $_timestamp;

    /*
     * Session generation and handling part
     */
    protected function __construct()
    {
        if(SESSION_ID == false) {
            $this->setCookie(generateKey(32));
        } elseif(!headers_sent()) {
            $this->setCookie(SESSION_ID);
        }
    }

    public function refreshCookie()
    {
        if(isset($_COOKIE['MOVIM_SESSION_ID'])) {
            $this->setCookie($_COOKIE['MOVIM_SESSION_ID']);
        }
    }

    public function renewCookie()
    {
        $this->setCookie(generateKey(32));
    }

    public function getTime()
    {
        return time()+$this->_max_age;
    }

    private function setCookie($key)
    {
        header_remove('Set-Cookie');
        setcookie("MOVIM_SESSION_ID", $key, $this->getTime(), '/'/*, BASE_HOST, APP_SECURED*/);
    }

    public static function start()
    {
        if(!isset(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
}
