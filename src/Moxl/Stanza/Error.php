<?php

namespace Moxl\Stanza;

class Error
{
    public static function notImplemented()
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $error = $dom->createElement('error');
        $error->setAttribute('type', 'cancel');

        $fni = $dom->createElementNS('urn:ietf:params:xml:ns:xmpp-stanzas', 'feature-not-implemented');
        $error->appendChild($fni);

        return $error;
    }
}
