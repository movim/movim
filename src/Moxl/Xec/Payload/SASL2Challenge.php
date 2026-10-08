<?php

namespace Moxl\Xec\Payload;

use Moxl\Stanza\Stream;

class SASL2Challenge extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $challenge = base64_decode((string)$stanza);
        $response = base64_encode(linker($this->sessionId)->authentication->challenge($challenge));

        $this->send(Stream::sasl2Response($response));
    }
}
