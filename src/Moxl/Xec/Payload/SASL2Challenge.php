<?php

namespace Moxl\Xec\Payload;

use Moxl\Authentication;

class SASL2Challenge extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $auth = Authentication::getInstance();
        $challenge = base64_decode((string)$stanza);

        $response = base64_encode($auth->challenge($challenge));

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $auth = $dom->createElementNS('urn:xmpp:sasl:2', 'response', $response);
        $dom->appendChild($auth);

        \Moxl\API::request($dom->saveXML($dom->documentElement));
    }
}
