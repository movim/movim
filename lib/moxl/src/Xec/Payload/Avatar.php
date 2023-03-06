<?php

namespace Moxl\Xec\Payload;

use Moxl\Xec\Action\Avatar\Get;

class Avatar extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $jid = baseJid((string)$parent->attributes()->from);

        $c = \App\Contact::firstOrNew(['id' => $jid]);

        if (isset($stanza->items->item->metadata->info)) {
            $info = $stanza->items->item->metadata->info->attributes();

            if ($info->id != $c->avatarhash) {
                $c->avatarhash = $info->id;
                $c->avatartype = 'urn:xmpp:avatar:metadata';
                $c->save();

                $g = new Get;
                $g->setTo($jid)
                  ->request();
            }
        }
    }
}
