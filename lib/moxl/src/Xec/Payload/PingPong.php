<?php

namespace Moxl\Xec\Payload;

use Moxl\Stanza\Ping;

class PingPong extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $to = (string)$parent->attributes()->from;
        $id = (string)$parent->attributes()->id;
        Ping::pong($to, $id);
    }
}
