<?php

namespace Moxl\Xec\Payload;

class BOB extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $this->pack([
            'from' => (string)$parent->attributes()->from,
            'id' => (string)$parent->attributes()->id,
            'cid' => (string)$stanza->attributes()->cid
        ]);

        $this->deliver();
    }
}
