<?php

namespace Moxl\Xec\Payload;

class SASLSuccess extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $this->deliver();

        \Moxl\Stanza\Stream::init(me()->session->host, me()->id);
    }
}
