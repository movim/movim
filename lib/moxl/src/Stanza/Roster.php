<?php

namespace Moxl\Stanza;

class Roster
{
    public static function get()
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('jabber:iq:roster', 'query');
        $xml = \Moxl\API::iqWrapper($query, false, 'get');
        \Moxl\API::request($xml);
    }

    /*
     * Add contact
     */
    public static function add($to, $name, $group)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $roster = $dom->createElementNS('jabber:iq:roster', 'query');

        $item = $dom->createElement('item');
        $item->setAttribute('jid', $to);

        if (!empty($name)) {
            $item->setAttribute('name', $name);
        }

        $roster->appendChild($item);

        if (!empty($group)) {
            $group = $dom->createElement('group', $group);
            $item->appendChild($group);
        }

        $xml = \Moxl\API::iqWrapper($roster, false, 'set');
        \Moxl\API::request($xml);
    }

    public static function update($to, $name, $group)
    {
        $xml = self::add($to, $name, $group);
        \Moxl\API::request($xml);
    }

    /*
     * Remove a contact
     */
    public static function remove($to)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $roster = $dom->createElementNS('jabber:iq:roster', 'query');

        $item = $dom->createElement('item');
        $item->setAttribute('jid', $to);
        $item->setAttribute('subscription', 'remove');
        $roster->appendChild($item);

        $xml = \Moxl\API::iqWrapper($roster, false, 'set');
        \Moxl\API::request($xml);
    }
}
