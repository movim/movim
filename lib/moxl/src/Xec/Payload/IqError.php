<?php

namespace Moxl\Xec\Payload;

use Moxl\Stanza\Iq;

class IqError extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        if ($stanza->attributes()->type != 'error') {
            $to = (string)$stanza->attributes()->from;
            $id = (string)$stanza->attributes()->id;
            Iq::error($to, $id);
        }
    }
}
