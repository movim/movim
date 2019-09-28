<?php

namespace Moxl\Stanza;

class Bookmark
{
    public static function get()
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub', 'pubsub');

        $items = $dom->createElement('items');
        $items->setAttribute('node', 'storage:bookmarks');
        $pubsub->appendChild($items);

        $xml = \Moxl\API::iqWrapper($pubsub, false, 'get');
        \Moxl\API::request($xml);
    }
}
