<?php

namespace Moxl\Xec\Payload;

class SASLSuccess extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $this->deliver();

        \Moxl\Stanza\Stream::init($this->me->session->host, $this->me->id);
    }
}
