<?php

namespace Moxl\Xec\Payload;

use Moxl\Authentication;
use Moxl\Stanza\Stream;

class SASLChallenge extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $auth = Authentication::getInstance();
        $challenge = base64_decode((string)$stanza);

        $this->send(Stream::saslChallenge(base64_encode($auth->challenge($challenge))));
    }
}
