<?php

namespace Moxl\Xec\Payload;

use Moxl\Stanza\Ping;

class PingPong extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $to = (string)$parent->attributes()->from;
        $id = (string)$parent->attributes()->id;

        $this->iq(Ping::pong(), to: $to, id: $id, type: 'result');
    }
}
