<?php

namespace Moxl\Xec\Payload;

use Movim\CurrentCalls;

class JingleProceed extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $from = (string)$parent->attributes()->from;
        $id = (string)$stanza->attributes()->id;

        CurrentCalls::getInstance()->start($from, $id);

        $this->pack($id, $from);
        $this->deliver();
    }
}
