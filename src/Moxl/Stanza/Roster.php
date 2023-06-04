<?php

namespace Moxl\Stanza;

class Roster
{
    public static function get()
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('jabber:iq:roster', 'query');
        \Moxl\API::request(\Moxl\API::iqWrapper($query, false, 'get'));
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

        \Moxl\API::request(\Moxl\API::iqWrapper($roster, false, 'set'));
    }

    public static function update($to, $name, $group)
    {
        \Moxl\API::request(self::add($to, $name, $group));
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

        \Moxl\API::request(\Moxl\API::iqWrapper($roster, false, 'set'));
    }
}
