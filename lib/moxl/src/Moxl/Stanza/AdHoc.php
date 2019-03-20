<?php

namespace Moxl\Stanza;

class AdHoc
{
    public static function get($to)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('http://jabber.org/protocol/disco#items', 'query');
        $query->setAttribute('node', 'http://jabber.org/protocol/commands');

        $xml = \Moxl\API::iqWrapper($query, $to, 'get');
        \Moxl\API::request($xml);
    }

    public static function command($to, $node)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('http://jabber.org/protocol/commands', 'command');
        $query->setAttribute('node', $node);
        $query->setAttribute('action', 'execute');

        $xml = \Moxl\API::iqWrapper($query, $to, 'set');
        \Moxl\API::request($xml);
    }

    public static function submit($to, $node, $data, $sessionid)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $command = $dom->createElementNS('http://jabber.org/protocol/commands', 'command');
        $dom->appendChild($command);
        $command->setAttribute('sessionid', $sessionid);
        $command->setAttribute('node', $node);

        $x = $dom->createElementNS('jabber:x:data', 'x');
        $x->setAttribute('type', 'submit');
        $command->appendChild($x);

        $xmpp = new \FormtoXMPP($data);
        $xmpp->create();
        $xmpp->appendToX($dom);

        $xml = \Moxl\API::iqWrapper($command, $to, 'set');
        \Moxl\API::request($xml);
    }
}
