<?php

namespace Moxl\Xec\Payload;

class StreamError extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $this->deliver();
    }
}
