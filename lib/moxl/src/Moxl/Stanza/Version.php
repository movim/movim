<?php

namespace Moxl\Stanza;

class Version
{
    public static function send($to, $id, $name, $version, $os)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('jabber:iq:version', 'query');

        $query->appendChild($dom->createElement('name', $name));
        $query->appendChild($dom->createElement('version', $version));
        $query->appendChild($dom->createElement('os', $os));

        $xml = \Moxl\API::iqWrapper($query, $to, 'result', $id);
        \Moxl\API::request($xml);
    }
}
