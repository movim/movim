<?php

namespace Moxl\Xec\Payload;

use Moxl\Xec\Action\Avatar\Get;

class AvatarMetadata extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $jid = baseJid((string)$parent->attributes()->from);

        $c = \App\Contact::firstOrNew(['id' => $jid]);

        if (isset($stanza->items->item->metadata->info)) {
            $hash = $stanza->items->item->metadata->info->attributes()->id;

            if ($hash != $c->avatarhash) {
                $g = new Get;
                $g->setTo($jid)
                  ->request();
            }
        }
    }
}
