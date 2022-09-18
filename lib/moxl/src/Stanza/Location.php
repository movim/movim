<?php

namespace Moxl\Stanza;

class Location
{
    public static function publish($geo)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub', 'pubsub');

        $publish = $dom->createElement('publish');
        $publish->setAttribute('node', 'http://jabber.org/protocol/geoloc');
        $pubsub->appendChild($publish);

        $item = $dom->createElement('item');
        $publish->appendChild($item);

        $geoloc = $dom->createElement('geoloc');
        $geoloc->setAttribute('xmlns', 'http://jabber.org/protocol/geoloc');
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

        // Publish option
        $publishOption = $dom->createElement('publish-option');
        $x = $dom->createElement('x');
        $x->setAttribute('xmlns', 'jabber:x:data');
        $x->setAttribute('type', 'submit');
        $publishOption->appendChild($x);

        $field = $dom->createElement('field');
        $field->setAttribute('var', 'FORM_TYPE');
        $field->setAttribute('type', 'hidden');
        $field->appendChild($dom->createElement('value', 'http://jabber.org/protocol/pubsub#publish-options'));
        $x->appendChild($field);

        $field = $dom->createElement('field');
        $field->setAttribute('var', 'pubsub#persist_items');
        $field->appendChild($dom->createElement('value', 'true'));
        $x->appendChild($field);

        $field = $dom->createElement('field');
        $field->setAttribute('var', 'pubsub#send_last_published_item');
        $field->appendChild($dom->createElement('value', 'on_sub_and_presence'));
        $x->appendChild($field);

        $field = $dom->createElement('field');
        $field->setAttribute('var', 'pubsub#deliver_payloads');
        $field->appendChild($dom->createElement('value', 'true'));
        $x->appendChild($field);

        $field = $dom->createElement('field');
        $field->setAttribute('var', 'pubsub#access_model');
        $field->appendChild($dom->createElement('value', 'roster'));
        $x->appendChild($field);

        $field = $dom->createElement('field');
        $field->setAttribute('var', 'pubsub#pubsub#notify_retract');
        $field->appendChild($dom->createElement('value', 'true'));
        $x->appendChild($field);

        $pubsub->appendChild($publishOption);

        $xml = \Moxl\API::iqWrapper($pubsub, false, 'set');
        \Moxl\API::request($xml);
    }
}
