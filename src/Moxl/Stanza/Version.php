<?php

namespace Moxl\Stanza;

class Version
{
    protected $keyType = 'string';
    public $incrementing = false;

    public static function get()
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        return $dom->createElementNS('jabber:iq:version', 'query');
    }

    public static function send(string $name, string $version, string $os)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('jabber:iq:version', 'query');

        $query->appendChild($dom->createElement('name', $name));
        $query->appendChild($dom->createElement('version', $version));
        $query->appendChild($dom->createElement('os', $os));

        return $query;
    }
}
