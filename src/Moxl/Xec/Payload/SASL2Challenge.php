<?php

namespace Moxl\Xec\Payload;

use Moxl\Authentication;
use Moxl\Stanza\Stream;

class SASL2Challenge extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $auth = Authentication::getInstance();
        $challenge = base64_decode((string)$stanza);
        $response = base64_encode($auth->challenge($challenge));

        $this->send(Stream::sasl2Response($response));
    }
}
