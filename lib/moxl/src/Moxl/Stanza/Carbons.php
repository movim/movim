<?php

// http://xmpp.org/extensions/xep-0280.html

namespace Moxl\Stanza;

class Carbons
{
    public static function enable()
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $xml = $dom->createElementNS('urn:xmpp:carbons:2', 'enable');

        $xml = \Moxl\API::iqWrapper($xml, false, 'set');
        \Moxl\API::request($xml);
    }

    public static function disable()
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $xml = $dom->createElementNS('urn:xmpp:carbons:2', 'disable');

        $xml = \Moxl\API::iqWrapper($xml, false, 'set');
        \Moxl\API::request($xml);
    }
}
