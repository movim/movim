<?php

namespace Moxl\Xec\Payload;

use Moxl\Stanza\Error;

class DiscoItems extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $jid = (string)$parent->attributes()->from;
        $id = (string)$parent->attributes()->id;

        // Global handler, to be completed
        Error::notImplemented($jid, $id);
    }
}
