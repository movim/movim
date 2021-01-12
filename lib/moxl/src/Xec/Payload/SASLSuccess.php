<?php

namespace Moxl\Xec\Payload;

class SASLSuccess extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $this->deliver();

        \Moxl\Stanza\Stream::init(\App\User::me()->session->host);
    }
}
