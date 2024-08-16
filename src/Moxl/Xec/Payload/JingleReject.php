<?php

namespace Moxl\Xec\Payload;

use Movim\CurrentCall;

class JingleReject extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        // We can only reject the current session
        $jingleSid = CurrentCall::getInstance()->id;
        if ($jingleSid && (string)$stanza->attributes()->id != $jingleSid) return;

        $this->pack([
            'from' => (string)$parent->attributes()->from,
            'id' => (string)$stanza->attributes()->id
        ]);
        $this->deliver();
    }
}
