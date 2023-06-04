<?php

namespace Moxl\Xec\Payload;

use Moxl\Stanza\Ping;

class PingPong extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $to = (string)$parent->attributes()->from;
        $id = (string)$parent->attributes()->id;
        Ping::pong($to, $id);
    }
}
