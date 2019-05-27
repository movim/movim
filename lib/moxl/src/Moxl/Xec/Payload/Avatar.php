<?php

namespace Moxl\Xec\Payload;

use Movim\Picture;

class Avatar extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $jid = explodeJid((string)$parent->attributes()->from)['jid'];

        $p = new Picture;
        $p->fromBase((string)$stanza->items->item->data);
        $p->set($jid);

        $this->event('vcard', \App\Contact::firstOrNew(['id' => $jid]));
    }
}
