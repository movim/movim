<?php

namespace Moxl\Stanza;

use Moxl\Utils;

class IqGateway
{
    static function get($to)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('jabber:iq:gateway', 'query');
        $xml = \Moxl\API::iqWrapper($query, $to, 'get');
        \Moxl\API::request($xml);
    }
}
