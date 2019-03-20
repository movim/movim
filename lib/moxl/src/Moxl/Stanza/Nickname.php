<?php

namespace Moxl\Stanza;

class Nickname
{
    public static function set($nickname)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub', 'pubsub');

        $publish = $dom->createElement('publish');
        $publish->setAttribute('node', 'http://jabber.org/protocol/nick');
        $pubsub->appendChild($publish);

        $item = $dom->createElement('item');
        $publish->appendChild($item);

        $nick = $dom->createElement('nick', $nickname);
        $nick->setAttribute('xmlns', 'http://jabber.org/protocol/nick');
        $item->appendChild($nick);

        $xml = \Moxl\API::iqWrapper($pubsub, false, 'set');
        \Moxl\API::request($xml);
    }
}
