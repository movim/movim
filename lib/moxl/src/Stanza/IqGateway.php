<?php

namespace Moxl\Stanza;

use Moxl\Utils;

class IqGateway
{
    public static function get($to)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('jabber:iq:gateway', 'query');
        $xml = \Moxl\API::iqWrapper($query, $to, 'get');
        \Moxl\API::request($xml);
    }

    public static function set($to, $prompt)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('jabber:iq:gateway', 'query');
        $query->appendChild($dom->createElementNS('jabber:iq:gateway', 'prompt', $prompt));
        $xml = \Moxl\API::iqWrapper($query, $to, 'set');
        \Moxl\API::request($xml);
    }
}
