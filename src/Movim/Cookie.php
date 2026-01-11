<?php
/*
 * SPDX-FileCopyrightText: 2010 Jaussoin Timothée
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Movim;

class Cookie
{
    public static function set()
    {
        $sessionId = $_COOKIE['MOVIM_SESSION_ID'];

        if ($sessionId == null) {
            self::renew();
        } else {
            self::setCookie($sessionId);
        }
    }

    public static function renew()
    {
        self::setCookie(generateKey(32));
    }

    public static function getTime()
    {
        return time() + 604800;
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
