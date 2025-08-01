<?php

namespace Moxl\Xec\Payload;

use App\Reported;

class Blocked extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $jid = (string)$stanza->item->attributes()->jid;

        $r = Reported::firstOrCreate(['id' => $jid]);
        me()->reported()->syncWithoutDetaching([$r->id => ['synced' => true]]);
        me()->refreshBlocked();

        $this->pack($jid);
        $this->deliver();
    }
}
