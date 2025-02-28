<?php

namespace Moxl\Xec\Payload;

class JingleAccept extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $this->pack((string)$stanza->attributes()->id, (string)$parent->attributes()->from);
        $this->deliver();
    }
}
