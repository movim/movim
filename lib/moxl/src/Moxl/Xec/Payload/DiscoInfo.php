<?php

namespace Moxl\Xec\Payload;

use Moxl\Stanza\Disco;

class DiscoInfo extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $jid = (string)$parent->attributes()->from;
        $id = (string)$parent->attributes()->id;

        Disco::answer($jid, $id);
    }
}
