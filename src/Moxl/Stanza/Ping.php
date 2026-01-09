<?php

namespace Moxl\Stanza;

class Ping
{
    public static function entity()
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $ping = $dom->createElementNS('urn:xmpp:ping', 'ping');

        return $ping;
    }

    public static function pong()
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $ping = $dom->createElementNS('urn:xmpp:ping', 'ping');

        return $ping;
    }
}
