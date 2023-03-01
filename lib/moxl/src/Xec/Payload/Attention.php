<?php

namespace Moxl\Xec\Payload;

class Attention extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $this->deliver();
    }
}
