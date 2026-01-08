<?php

namespace Moxl\Stanza;

use Moxl\Utils;

class Disco
{
    public static function answer(string $node)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('http://jabber.org/protocol/disco#info', 'query');
        $query->setAttribute('node', $node);

        $identityData = Utils::getIdentity();

        $identity = $dom->createElement('identity');
        $identity->setAttribute('category', $identityData->category);
        $identity->setAttribute('type', $identityData->type);
        $identity->setAttribute('name', $identityData->name);

        $query->appendChild($identity);

        foreach (Utils::getSupportedServices() as $service) {
            $feature = $dom->createElement('feature');
            $feature->setAttribute('var', $service);
            $query->appendChild($feature);
        }

        return $query;
    }

    public static function request($node = false)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('http://jabber.org/protocol/disco#info', 'query');

        if ($node != false) {
            $query->setAttribute('node', $node);
        }

        return $query;
    }

    public static function items($node = false)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('http://jabber.org/protocol/disco#items', 'query');

        if ($node != false) {
            $query->setAttribute('node', $node);
        }

        return $query;
    }
}
