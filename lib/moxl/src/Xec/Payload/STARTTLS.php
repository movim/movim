<?php

namespace Moxl\Xec\Payload;

use Moxl\Stanza\Stream;

class STARTTLS extends Payload
{
    public function handle($stanza, $parent = false)
    {
        if (isset($stanza->required)) {
            Stream::startTLS();
        }
    }
}
