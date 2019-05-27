<?php

namespace Moxl\Xec\Payload;

use Moxl\Xec\Action\PubsubSubscription\Get;

class PubsubSubscription extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $from = explodeJid((string)$parent->attributes()->from)['jid'];

        $g = new Get;
        $g->setTo($from)
          ->request();
    }
}
