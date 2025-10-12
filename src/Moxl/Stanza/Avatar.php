<?php

namespace Moxl\Stanza;

class Avatar
{
    public const NODE_DATA = 'urn:xmpp:avatar:data';
    public const NODE_METADATA = 'urn:xmpp:avatar:metadata';
    public static $nodeConfig = [
        'FORM_TYPE' => 'http://jabber.org/protocol/pubsub#publish-options',
        'pubsub#persist_items' => 'true',
        'pubsub#access_model' => 'presence',
        'pubsub#send_last_published_item' => 'on_sub_and_presence',
        'pubsub#deliver_payloads' => 'true',
        'pubsub#max_items' => '1',
    ];

    public static function get($to, $node = false)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub', 'pubsub');

        $items = $dom->createElement('items');
        $items->setAttribute('node', $node ? $node : self::NODE_DATA);
        $pubsub->appendChild($items);

        \Moxl\API::request(\Moxl\API::iqWrapper($pubsub, $to, 'get'));
    }

    public static function set($data, $to = false, $node = false, bool $withPublishOption = true)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElement('pubsub');
        $pubsub->setAttribute('xmlns', 'http://jabber.org/protocol/pubsub');

        $publish = $dom->createElement('publish');
        $publish->setAttribute('node', $node ? $node : self::NODE_DATA);
        $pubsub->appendChild($publish);

        $item = $dom->createElement('item');
        $item->setAttribute('id', $node ? self::NODE_DATA : sha1(base64_decode($data)));
        $publish->appendChild($item);

        $data = $dom->createElement('data', $data);
        $data->setAttribute('xmlns', self::NODE_DATA);
        $item->appendChild($data);

        if ($withPublishOption) {
            $publishOption = $dom->createElement('publish-options');
            $x = $dom->createElement('x');
            $x->setAttribute('xmlns', 'jabber:x:data');
            $x->setAttribute('type', 'submit');
            $publishOption->appendChild($x);

            \Moxl\Utils::injectConfigInX($x, self::$nodeConfig);

            $pubsub->appendChild($publishOption);
        }

        \Moxl\API::request(\Moxl\API::iqWrapper($pubsub, $to, 'set'));
    }

    public static function setMetadata($data, $url = false, $to = false, $node = false, $width = 512, $height = 512, bool $withPublishOption = true)
    {
        $decoded = base64_decode($data);

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElement('pubsub');
        $pubsub->setAttribute('xmlns', 'http://jabber.org/protocol/pubsub');

        $publish = $dom->createElement('publish');
        $publish->setAttribute('node', $node ? $node : self::NODE_METADATA);
        $pubsub->appendChild($publish);

        $item = $dom->createElement('item');
        $item->setAttribute('id', $node ? self::NODE_METADATA : sha1($decoded));
        $publish->appendChild($item);

        $metadata = $dom->createElement('metadata');
        $metadata->setAttribute('xmlns', self::NODE_METADATA);
        $item->appendChild($metadata);

        $info = $dom->createElement('info');

        if ($url) {
            $info->setAttribute('url', $url);
        }

        $info->setAttribute('width', $width);
        $info->setAttribute('height', $height);
        $info->setAttribute('type', 'image/jpeg');
        $info->setAttribute('id', sha1($decoded));
        $info->setAttribute('bytes', strlen($decoded));
        $metadata->appendChild($info);

        if ($withPublishOption) {
            $publishOption = $dom->createElement('publish-options');
            $x = $dom->createElement('x');
            $x->setAttribute('xmlns', 'jabber:x:data');
            $x->setAttribute('type', 'submit');
            $publishOption->appendChild($x);

            \Moxl\Utils::injectConfigInX($x, self::$nodeConfig);

            $pubsub->appendChild($publishOption);
        }

        \Moxl\API::request(\Moxl\API::iqWrapper($pubsub, $to, 'set'));
    }
}
