<?php

namespace Moxl\Stanza;

use Movim\Session;

class Ping
{
    public static function server()
    {
        $session = Session::start();
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $ping = $dom->createElementNS('urn:xmpp:ping', 'ping');
        \Moxl\API::request(\Moxl\API::iqWrapper($ping, $session->get('host'), 'get'));
    }

    public static function pong($to, $id)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $ping = $dom->createElementNS('urn:xmpp:ping', 'ping');
        \Moxl\API::request(\Moxl\API::iqWrapper($ping, $to, 'result', $id));
    }
}
