<?php

namespace Movim;

class Cookie
{
    public static function set()
    {
        if (SESSION_ID == false) {
            self::renew();
        } else {
            self::setCookie(SESSION_ID);
        }
    }

    public static function refresh()
    {
        if (isset($_COOKIE['MOVIM_SESSION_ID'])) {
            self::setCookie($_COOKIE['MOVIM_SESSION_ID']);
        }
    }

    public static function renew()
    {
        self::setCookie(generateKey(32));
    }

    public static function getTime()
    {
        return time()+604800;
    }

    public static function clearCookieHeader()
    {
        header_remove('Set-Cookie');
    }

    private static function setCookie($key)
    {
        if (!headers_sent()) {
            self::clearCookieHeader();
            setcookie('MOVIM_SESSION_ID', $key, [
                'expires' => self::getTime(),
                'path' => '/',
                'secure' => true,
                'samesite' => 'lax',
            ]);
        }
    }
}
