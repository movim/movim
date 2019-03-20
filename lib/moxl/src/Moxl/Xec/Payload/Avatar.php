<?php

namespace Moxl\Xec\Payload;

use Movim\Picture;

class Avatar extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $jid = current(explode('/', (string)$parent->attributes()->from));

        $p = new Picture;
        $p->fromBase((string)$stanza->items->item->data);
        $p->set($jid);

        $this->event('vcard', \App\Contact::firstOrNew(['id' => $jid]));
    }
}
