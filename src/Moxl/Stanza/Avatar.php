<?php

namespace Moxl\Stanza;

class Avatar {
    static function get($to)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub', 'pubsub');

        $items = $dom->createElement('items');
        $items->setAttribute('node', 'urn:xmpp:avatar:data');
        $pubsub->appendChild($items);

        $xml = \Moxl\API::iqWrapper($pubsub, $to, 'get');
        \Moxl\API::request($xml);
    }

    static function set($data)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub', 'pubsub');

        $publish = $dom->createElement('publish');
        $publish->setAttribute('node', 'urn:xmpp:avatar:data');
        $pubsub->appendChild($publish);

        $item = $dom->createElement('item');
        $item->setAttribute('id', sha1($data));
        $publish->appendChild($item);

        $data = $dom->createElementNS('urn:xmpp:avatar:data', 'data', $data);
        $item->appendChild($data);

        $xml = \Moxl\API::iqWrapper($pubsub, false, 'set');
        \Moxl\API::request($xml);
    }

    static function setMetadata($data)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub', 'pubsub');

        $publish = $dom->createElement('publish');
        $publish->setAttribute('node', 'urn:xmpp:avatar:metadata');
        $pubsub->appendChild($publish);

        $item = $dom->createElement('item');
        $item->setAttribute('id', sha1($data));
        $publish->appendChild($item);

        $metadata = $dom->createElementNS('urn:xmpp:avatar:data', 'metadata');
        $item->appendChild($metadata);

        $info = $dom->createElement('info');
        $info->setAttribute('height', '410');
        $info->setAttribute('width', '410');
        $info->setAttribute('type', 'image/jpeg');
        $info->setAttribute('id', sha1($data));
        $info->setAttribute('bytes', strlen(base64_decode($data)));
        $metadata->appendChild($info);

        $xml = \Moxl\API::iqWrapper($pubsub, false, 'set');
        \Moxl\API::request($xml);
    }
}
