<?php

namespace Moxl\Xec\Payload;

use Moxl\Stanza\Disco;
use Moxl\Utils;

class DiscoInfo extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        if ($parent->attributes()->type == 'get') {
            $jid = (string)$parent->attributes()->from;
            $id = (string)$parent->attributes()->id;

            Disco::answer($jid, $id, (string)$stanza->attributes()->node);
        }
    }
}
