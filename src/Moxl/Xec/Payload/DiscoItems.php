<?php

namespace Moxl\Xec\Payload;

use Moxl\Stanza\Error;

class DiscoItems extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $jid = (string)$parent->attributes()->from;
        $id = (string)$parent->attributes()->id;

        // Global handler, to be completed
        $this->iq(Error::notImplemented(), to: $jid, id: $id, type: 'error');
    }
}
