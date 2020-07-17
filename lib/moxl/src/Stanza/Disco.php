<?php

namespace Moxl\Stanza;

use Moxl\Utils;

class Disco
{
    public static function answer($to, $id)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('http://jabber.org/protocol/disco#info', 'query');
        $query->setAttribute('node', 'https://movim.eu/#'.Utils::generateCaps());

        $identity = $dom->createElement('identity');
        $identity->setAttribute('category', 'client');
        $identity->setAttribute('type', 'web');
        $identity->setAttribute('name', 'Movim');

        $query->appendChild($identity);

        foreach (Utils::getSupportedServices() as $service) {
            $feature = $dom->createElement('feature');
            $feature->setAttribute('var', $service);
            $query->appendChild($feature);
        }

        $xml = \Moxl\API::iqWrapper($query, $to, 'result', $id);
        \Moxl\API::request($xml);
    }

    public static function request($to, $node = false)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('http://jabber.org/protocol/disco#info', 'query');

        if ($node != false) {
            $query->setAttribute('node', $node);
        }

        $xml = \Moxl\API::iqWrapper($query, $to, 'get');
        \Moxl\API::request($xml);
    }

    public static function items($to, $node = false)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('http://jabber.org/protocol/disco#items', 'query');

        if ($node != false) {
            $query->setAttribute('node', $node);
        }

        $xml = \Moxl\API::iqWrapper($query, $to, 'get');
        \Moxl\API::request($xml);
    }
}
