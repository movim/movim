<?php

namespace Moxl\Xec\Payload;

class Unblocked extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $jid = (string)$stanza->item->attributes()->jid;

        me()->reported()->detach($jid);
        me()->refreshBlocked();

        $this->pack($jid);
        $this->deliver();
    }
}
