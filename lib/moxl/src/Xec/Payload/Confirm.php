<?php

namespace Moxl\Xec\Payload;

class Confirm extends Payload
{
    public function handle($stanza, $parent = false)
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
