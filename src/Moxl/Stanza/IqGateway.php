<?php

namespace Moxl\Stanza;

class IqGateway
{
    public static function get()
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('jabber:iq:gateway', 'query');

        return $query;
    }

    public static function set($prompt)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('jabber:iq:gateway', 'query');
        $query->appendChild($dom->createElementNS('jabber:iq:gateway', 'prompt', $prompt));

        return $query;
    }
}
