<?php

namespace Moxl\Xec\Payload;

use Moxl\Stanza\Disco;

class DiscoInfo extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        if ($parent->attributes()->type == 'get') {
            $jid = (string)$parent->attributes()->from;
            $id = (string)$parent->attributes()->id;

            $this->iq(Disco::answer((string)$stanza->attributes()->node), to: $jid, id: $id, type: 'result');
        }
    }
}
