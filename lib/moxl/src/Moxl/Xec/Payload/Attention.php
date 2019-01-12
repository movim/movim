<?php

namespace Moxl\Xec\Payload;

class Attention extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $this->deliver();
    }
}
