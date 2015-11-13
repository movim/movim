<?php

namespace Moxl\Stanza;

class Roster {
    /*
     * The roster builder
     */
    static function builder($xml, $to, $type)
    {
        $xml = '
            <query xmlns="jabber:iq:roster">
                '.$xml.'
            </query>';

        $xml = \Moxl\API::iqWrapper($xml, $to, $type);
        \Moxl\API::request($xml);
    }

    static function get()
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('jabber:iq:roster', 'query');
        $xml = \Moxl\API::iqWrapper($query, false, 'get');
        \Moxl\API::request($xml);
    }

    /*
     * Add contact
     */
    static function add($to, $name, $group)
    {
        $xml ='
            <item
                jid="'.$to.'"
                name="'.htmlspecialchars($name).'">
                <group>'.htmlspecialchars($group).'</group>
            </item>';

        $xml = self::builder($xml, false, 'set');
        \Moxl\API::request($xml);
    }

    static function update($to, $name, $group)
    {
        $xml = self::add($to, $name, $group);
        \Moxl\API::request($xml);
    }

    /*
     * Remove a contact
     */
    static function remove($to)
    {
        $xml = '
            <item jid="'.$to.'" subscription="remove"/>';

        $xml = self::builder($xml, false, 'set');
        \Moxl\API::request($xml);
    }

}
