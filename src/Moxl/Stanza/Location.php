<?php

namespace Moxl\Stanza;

class Location
{
    public static $node = 'http://jabber.org/protocol/geoloc';
    public static $nodeConfig = [
        'FORM_TYPE' => 'http://jabber.org/protocol/pubsub#publish-options',
        'pubsub#persist_items' => 'true',
        'pubsub#access_model' => 'roster',
        'pubsub#send_last_published_item' => 'on_sub_and_presence',
        'pubsub#deliver_payloads' => 'true',
        'pubsub#max_items' => '1',
        'pubsub#notify_retract' => 'true',
    ];

    public static function publish(array $geo, bool $withPublishOption = true)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub', 'pubsub');

        $publish = $dom->createElement('publish');
        $publish->setAttribute('node', self::$node);
        $pubsub->appendChild($publish);

        $item = $dom->createElement('item');
        $publish->appendChild($item);

        $geoloc = $dom->createElement('geoloc');
        $geoloc->setAttribute('xmlns', self::$node);
        $item->appendChild($geoloc);

        if (isset($geo['latitude'])) {
            $lat = $dom->createElement('lat', $geo['latitude']);
            $geoloc->appendChild($lat);
        }

        if (isset($geo['longitude'])) {
            $lon = $dom->createElement('lon', $geo['longitude']);
            $geoloc->appendChild($lon);
        }

        if (isset($geo['accuracy'])) {
            $accuracy = $dom->createElement('accuracy', $geo['accuracy']);
            $geoloc->appendChild($accuracy);
        }

        $timestamp = $dom->createElement('timestamp', gmdate('c'));
        $geoloc->appendChild($timestamp);

        if ($withPublishOption) {
            $publishOption = $dom->createElement('publish-options');
            $x = $dom->createElement('x');
            $x->setAttribute('xmlns', 'jabber:x:data');
            $x->setAttribute('type', 'submit');
            $publishOption->appendChild($x);

            \Moxl\Utils::injectConfigInX($x, self::$nodeConfig);

            $pubsub->appendChild($publishOption);
        }

        \Moxl\API::request(\Moxl\API::iqWrapper($pubsub, false, 'set'));
    }
}
