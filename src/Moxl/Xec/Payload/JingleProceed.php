<?php

namespace Moxl\Xec\Payload;

use Movim\CurrentCall;

class JingleProceed extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $from = (string)$parent->attributes()->from;
        $id = (string)$stanza->attributes()->id;

        CurrentCall::getInstance()->start($from, $id);

        $this->pack([
            'from' => $from,
            'id' => $id
        ]);

        $this->deliver();
    }
}
