<?php

namespace Moxl\Xec\Payload;

class BOB extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $from = (string)$parent->attributes()->from;
        $cid = (string)$stanza->attributes()->cid;
        $id = (string)$parent->attributes()->id;

        $this->pack([$from, $id, $cid]);
        $this->deliver();
    }
}
