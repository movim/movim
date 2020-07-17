<?php

namespace Moxl\Stanza;

class Avatar
{
    public static function get($to)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub', 'pubsub');

        $items = $dom->createElement('items');
        $items->setAttribute('node', 'urn:xmpp:avatar:data');
        $pubsub->appendChild($items);

        $xml = \Moxl\API::iqWrapper($pubsub, $to, 'get');
        \Moxl\API::request($xml);
    }

    public static function set($data, $to = false, $node = false)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElement('pubsub');
        $pubsub->setAttribute('xmlns', 'http://jabber.org/protocol/pubsub');

        $publish = $dom->createElement('publish');
        $publish->setAttribute('node', $node ? $node : 'urn:xmpp:avatar:data');
        $pubsub->appendChild($publish);

        $item = $dom->createElement('item');
        $item->setAttribute('id', $node ? 'urn:xmpp:avatar:data' : sha1(base64_decode($data)));
        $publish->appendChild($item);

        $data = $dom->createElement('data', $data);
        $data->setAttribute('xmlns', 'urn:xmpp:avatar:data');
        $item->appendChild($data);

        $xml = \Moxl\API::iqWrapper($pubsub, $to, 'set');
        \Moxl\API::request($xml);
    }

    public static function setMetadata($data, $url = false, $to = false, $node = false)
    {
        $decoded = base64_decode($data);

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElement('pubsub');
        $pubsub->setAttribute('xmlns', 'http://jabber.org/protocol/pubsub');

        $publish = $dom->createElement('publish');
        $publish->setAttribute('node', $node ? $node : 'urn:xmpp:avatar:metadata');
        $pubsub->appendChild($publish);

        $item = $dom->createElement('item');
        $item->setAttribute('id', $node ? 'urn:xmpp:avatar:metadata' : sha1($decoded));
        $publish->appendChild($item);

        $metadata = $dom->createElement('metadata');
        $metadata->setAttribute('xmlns', 'urn:xmpp:avatar:metadata');
        $item->appendChild($metadata);

        $info = $dom->createElement('info');

        if ($url) {
            $info->setAttribute('url', $url);
        }

        $info->setAttribute('height', '410');
        $info->setAttribute('width', '410');
        $info->setAttribute('type', 'image/jpeg');
        $info->setAttribute('id', sha1($decoded));
        $info->setAttribute('bytes', strlen($decoded));
        $metadata->appendChild($info);

        $xml = \Moxl\API::iqWrapper($pubsub, $to, 'set');
        \Moxl\API::request($xml);
    }
}
