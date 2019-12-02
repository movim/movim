<?php

namespace Moxl\Xec\Payload;

class JinglePropose extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $this->pack([
            'from' => (string)$parent->attributes()->from,
            'id' => (string)$stanza->attributes()->id
        ]);
        $this->deliver();
    }
}
