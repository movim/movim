<?php

namespace Moxl\Xec\Payload;

use Movim\Image;

class AvatarData extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $jid = baseJid((string)$parent->attributes()->from);

        $p = new Image;
        $p->fromBase((string)$stanza->items->item->data);
        $p->setKey($jid);
        $p->save();

        $this->pack($jid);
        $this->event('vcard');
    }
}