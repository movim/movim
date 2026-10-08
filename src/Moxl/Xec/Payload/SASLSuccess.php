<?php

namespace Moxl\Xec\Payload;

use Moxl\Stanza\Stream;

class SASLSuccess extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        linker($this->sessionId)->authentication->clear();

        $this->deliver();
        $this->me->refresh();

        linker($this->sessionId)->attachUser($this->me);
        linker($this->sessionId)->writeXMPP(Stream::init($this->me->session->host));
    }
}
