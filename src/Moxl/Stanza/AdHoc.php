<?php

namespace Moxl\Stanza;

class AdHoc {
    static function get($to)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('http://jabber.org/protocol/disco#items', 'query');
        $query->setAttribute('node', 'http://jabber.org/protocol/commands');

        $xml = \Moxl\API::iqWrapper($query, $to, 'get');
        \Moxl\API::request($xml);
    }

    static function command($to, $node)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('http://jabber.org/protocol/commands', 'command');
        $query->setAttribute('node', $node);
        $query->setAttribute('action', 'execute');

        $xml = \Moxl\API::iqWrapper($query, $to, 'set');
        \Moxl\API::request($xml);
    }

    static function submit($to, $node, $data, $sessionid)
    {
        $xmpp = new \FormtoXMPP();
        $stream = '
            <command xmlns="http://jabber.org/protocol/commands"
                   sessionid="'.$sessionid.'"
                   node="'.$node.'">
                <x xmlns="jabber:x:data" type="submit"></x>
            </command>';
        $xml = $xmpp->getXMPP($stream, $data)->asXML();
        $xml = \Moxl\API::iqWrapper(strstr($xml, '<command'), $to, 'set');
        \Moxl\API::request($xml);
    }
}
