<?php

namespace Moxl\Xec\Payload;

use Moxl\Authentication;

class SASLChallenge extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $auth = Authentication::getInstance();
        $challenge = base64_decode((string)$stanza);

        $response = base64_encode($auth->challenge($challenge));

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $auth = $dom->createElementNS('urn:ietf:params:xml:ns:xmpp-sasl', 'response', $response);
        $dom->appendChild($auth);

        \Moxl\API::request($dom->saveXML($dom->documentElement));
    }
}
