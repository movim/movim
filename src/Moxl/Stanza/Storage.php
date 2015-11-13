<?php
/*
 * Basic stanza for the XEP-0049 implementation
 */

namespace Moxl\Stanza;

class Storage {
    static function set($xmlns, $data)
    {
        $dom = new \DOMDocument('1.0', 'utf-8');
        $query = $dom->createElementNS('jabber:iq:private', 'query');
        $data = $dom->createElementNS($xmlns, 'data', $data);
        $query->appendchild($data);

        $xml = \Moxl\API::iqWrapper($query, false, 'set');
        \Moxl\API::request($xml);
    }

    static function get($xmlns)
    {
        $dom = new \DOMDocument('1.0', 'utf-8');
        $query = $dom->createElementNS('jabber:iq:private', 'query');
        $data = $dom->createElementNS($xmlns, 'data');
        $query->appendchild($data);

        $xml = \Moxl\API::iqWrapper($query, false, 'get');
        \Moxl\API::request($xml);
    }

}
