<?php

namespace Moxl\Stanza;

use Moxl\Utils;

class ExternalServices
{
    public static function request($to)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('urn:xmpp:extdisco:2', 'services');

        $xml = \Moxl\API::iqWrapper($query, $to, 'get');
        \Moxl\API::request($xml);
    }
}