<?php
/*
 * Basic stanza for the XEP-0049 implementation
 */

namespace Moxl\Stanza;

class Storage
{
    private static function prepareQuery($xmlns, $data = false)
    {
        $dom = new \DOMDocument('1.0', 'utf-8');
        $query = $dom->createElementNS('jabber:iq:private', 'query');
        $data = ($data)
            ? $dom->createElement('data', $data)
            : $dom->createElement('data');
        $data->setAttribute('xmlns', $xmlns);
        $query->appendchild($data);

        return $query;
    }

    public static function set($xmlns, $data)
    {
        $xml = \Moxl\API::iqWrapper(self::prepareQuery($xmlns, $data), false, 'set');
        \Moxl\API::request($xml);
    }

    public static function get($xmlns)
    {
        $xml = \Moxl\API::iqWrapper(self::prepareQuery($xmlns), false, 'get');
        \Moxl\API::request($xml);
    }
}
