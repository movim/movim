<?php

namespace Moxl\Xec\Payload;

class Unblocked extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $jid = (string)$stanza->item->attributes()->jid;

        \App\User::me()->reported()->detach($jid);
        \App\User::me()->refreshBlocked();

        $this->pack($jid);
        $this->deliver();
    }
}
