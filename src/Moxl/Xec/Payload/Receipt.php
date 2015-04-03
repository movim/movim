<?php

namespace Moxl\Xec\Payload;

class Receipt extends Payload
{
    public function handle($stanza, $parent = false) {        
        $from = (string)$parent->attributes()->from;
        $id = (string)$parent->attributes()->id;

        \Moxl\Stanza\Message::receipt($from, $id);
    }
}
