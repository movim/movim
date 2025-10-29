<?php

namespace Moxl\Stanza;

class Iq
{
    public static function error(string $to, string $id)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $error = $dom->createElement('error');
        $error->setAttribute('type', 'cancel');

        $error->appendChild($dom->createElementNS('urn:ietf:params:xml:ns:xmpp-stanzas', 'service-unavailable'));

        \Moxl\API::request(\Moxl\API::iqWrapper($error, $to, 'error', $id));
    }
}
