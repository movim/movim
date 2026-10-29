<?php

namespace Moxl\Stanza;

class Iq
{
    public static function error()
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $error = $dom->createElement('error');
        $error->setAttribute('type', 'cancel');

        $serviceUnavailable = $dom->createElement('service-unavailable');
        $serviceUnavailable->setAttribute('xmlns', 'urn:ietf:params:xml:ns:xmpp-stanzas');

        $error->appendChild($serviceUnavailable);

        return $error;
    }
}
