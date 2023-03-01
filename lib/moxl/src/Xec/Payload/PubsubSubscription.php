<?php

namespace Moxl\Xec\Payload;

use Moxl\Xec\Action\PubsubSubscription\Get;

class PubsubSubscription extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $from = baseJid((string)$parent->attributes()->from);

        $g = new Get;
        $g->setTo($from)
          ->request();
    }
}
