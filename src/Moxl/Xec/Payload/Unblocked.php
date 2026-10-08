<?php

namespace Moxl\Xec\Payload;

class Unblocked extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $jid = (string)$stanza->item->attributes()->jid;

        $this->me->reported()->detach($jid);
        $this->me->refreshBlocked();

        $this->pack($jid);
        $this->deliver();
    }
}
