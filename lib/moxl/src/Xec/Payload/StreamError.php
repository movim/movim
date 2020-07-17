<?php

namespace Moxl\Xec\Payload;

use Moxl\Stanza\Stream;

class StreamError extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $this->deliver();
    }
}
