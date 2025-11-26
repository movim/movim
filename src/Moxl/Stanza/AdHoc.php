<?php

namespace Moxl\Stanza;

class AdHoc
{
    public static function get($to)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('http://jabber.org/protocol/disco#items', 'query');
        $query->setAttribute('node', 'http://jabber.org/protocol/commands');

        \Moxl\API::request(\Moxl\API::iqWrapper($query, $to, 'get'));
    }

    public static function command($to, string $node)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('http://jabber.org/protocol/commands', 'command');
        $query->setAttribute('node', $node);
        $query->setAttribute('action', 'execute');

        \Moxl\API::request(\Moxl\API::iqWrapper($query, $to, 'set'));
    }

    public static function submit($to, string $node, array $data, string $sessionid)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $command = $dom->createElementNS('http://jabber.org/protocol/commands', 'command');
        $dom->appendChild($command);
        $command->setAttribute('sessionid', $sessionid);
        $command->setAttribute('node', $node);
        $command->setAttribute('action', 'complete');

        $x = $dom->createElement('x');
        $x->setAttribute('xmlns', 'jabber:x:data');
        $x->setAttribute('type', 'submit');
        $command->appendChild($x);

        \Moxl\Utils::injectConfigInX($x, $data);

        \Moxl\API::request(\Moxl\API::iqWrapper($command, $to, 'set'));
    }
}
