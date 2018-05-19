<?php

namespace Moxl\Xec\Payload;

use Moxl\Stanza\Disco;

class DiscoInfo extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $jid = (string)$parent->attributes()->from;
        $to = current(explode('/',(string)$parent->attributes()->to));
        $id = (string)$parent->attributes()->id;

        Disco::answer($jid, $id);
    }
}
