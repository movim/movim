<?php

namespace Moxl\Xec\Payload;

class SASLSuccess extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $this->deliver();

        \Moxl\Stanza\Stream::init(\App\User::me()->session->host, \App\User::me()->id);
    }
}
