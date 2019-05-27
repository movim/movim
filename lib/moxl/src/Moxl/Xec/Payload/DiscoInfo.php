<?php

namespace Moxl\Xec\Payload;

use Moxl\Stanza\Disco;

class DiscoInfo extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $jid = (string)$parent->attributes()->from;
        $to = explodeJid((string)$parent->attributes()->to)['jid'];
        $id = (string)$parent->attributes()->id;

        Disco::answer($jid, $id);
    }
}
