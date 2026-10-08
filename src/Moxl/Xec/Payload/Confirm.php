<?php

namespace Moxl\Xec\Payload;

class Confirm extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $this->pack([
            'from' => (string)$parent->attributes()->from,
            'id' => (string)$stanza->attributes()->id,
            'url' => (string)$stanza->attributes()->url,
            'method' => (string)$stanza->attributes()->method
        ]);

        $this->deliver();
    }
}
