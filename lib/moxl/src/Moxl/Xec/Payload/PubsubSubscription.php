<?php

namespace Moxl\Xec\Payload;

use Moxl\Xec\Action\PubsubSubscription\Get;

class PubsubSubscription extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $from = current(explode('/', (string)$parent->attributes()->from));

        $g = new Get;
        $g->setTo($from)
          ->request();
    }
}
