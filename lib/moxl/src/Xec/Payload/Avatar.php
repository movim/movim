<?php

namespace Moxl\Xec\Payload;

use Movim\Image;

class Avatar extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $jid = current(explode('/', (string)$parent->attributes()->from));

        $p = new Image;
        $p->fromBase((string)$stanza->items->item->data);
        $p->setKey($jid);
        $p->save();

        $this->event('vcard', \App\Contact::firstOrNew(['id' => $jid]));
    }
}
