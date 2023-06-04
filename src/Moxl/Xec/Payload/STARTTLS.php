<?php

namespace Moxl\Xec\Payload;

use Moxl\Stanza\Stream;

class STARTTLS extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        if (isset($stanza->required)) {
            Stream::startTLS();
        }
    }
}
