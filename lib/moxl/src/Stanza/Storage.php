<?php
/*
 * Basic stanza for the XEP-0223 implementation
 */

namespace Moxl\Stanza;

class Storage
{
    public static function publish($xmlns, $data)
    {
        $dom = new \DOMDocument('1.0', 'utf-8');

        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub', 'pubsub');
        $publish = $dom->createElement('publish');
        $publish->setAttribute('node', $xmlns);
        $pubsub->appendChild($publish);

        $item = $dom->createElement('item');
        $item->setAttribute('id', 'current');
        $item->appendChild($dom->createElement('data', $data));
        $publish->appendChild($item);

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

        $field->setAttribute('var', 'pubsub#access_model');
        $field->appendChild($dom->createElement('value', 'whitelist'));
        $x->appendChild($field);

        $pubsub->appendChild($publishOption);

        $xml = \Moxl\API::iqWrapper($pubsub, false, 'set');
        \Moxl\API::request($xml);
    }
}
