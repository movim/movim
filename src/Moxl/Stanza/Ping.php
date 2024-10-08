<?php

namespace Moxl\Stanza;

use Movim\Session;

class Ping
{
    public static function entity(?string $to = null)
    {
        $session = Session::instance();
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $ping = $dom->createElementNS('urn:xmpp:ping', 'ping');
        \Moxl\API::request(\Moxl\API::iqWrapper($ping, $to ?? $session->get('host'), 'get'));
    }

    public static function pong($to, $id)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $ping = $dom->createElementNS('urn:xmpp:ping', 'ping');
        \Moxl\API::request(\Moxl\API::iqWrapper($ping, $to, 'result', $id));
    }
}
