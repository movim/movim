<?php

// http://xmpp.org/extensions/xep-0280.html

namespace Moxl\Stanza;

class Carbons
{
    public static function enable()
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $xml = $dom->createElementNS('urn:xmpp:carbons:2', 'enable');

        return $xml;
    }

    public static function disable()
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $xml = $dom->createElementNS('urn:xmpp:carbons:2', 'disable');

        return $xml;
    }
}
