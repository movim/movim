<?php

namespace Moxl\Xec\Payload;

use Moxl\Stanza\Stream;

class SASLChallenge extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $challenge = base64_decode((string)$stanza);

        $this->send(Stream::saslChallenge(
            base64_encode(linker($this->sessionId)->authentication->challenge($challenge))
        ));
    }
}
