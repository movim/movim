<?php

namespace Moxl\Xec\Payload;

use Moxl\Stanza\Iq;

class IqError extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $to = (string)$stanza->attributes()->from;
        $id = (string)$stanza->attributes()->id;
        Iq::error($to, $id);
    }
}
