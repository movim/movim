<?php

namespace Moxl\Xec\Payload;

class SASLSuccess extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $this->deliver();

        list($username, $host) = explode('@', $this->me->id);
        \Moxl\Stanza\Stream::init($host, $this->me->id);
    }
}
