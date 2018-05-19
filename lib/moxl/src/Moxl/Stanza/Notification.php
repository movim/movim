<?php

namespace Moxl\Stanza;

class Notification {
    static function get($to)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub', 'pubsub');
        $items = $dom->createElementNS('items');
        $items->setAttribute('node', 'urn:xmpp:inbox');

        $pubsub->appendChild($items);

        $xml = \Moxl\API::iqWrapper($pubsub, $to, 'get');
        \Moxl\API::request($xml);
    }

    static function itemDelete($to, $id)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub', 'pubsub');
        $retract = $dom->createElement('retract');
        $retract->setAttribute('node', 'urn:xmpp:inbox');
        $retract->setAttribute('notify', 'true');

        $item = $dom->createElement('item');
        $item->setAttribute('id', $id);

        $retract->appendChild($item);
        $pubsub->appendChild($retract);

        $xml = \Moxl\API::iqWrapper($pubsub, $to, 'set');
        \Moxl\API::request($xml);
    }
}
