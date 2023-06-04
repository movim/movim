<?php

namespace Moxl\Xec\Payload;

use Movim\Image;

class Banner extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $jid = baseJid((string)$parent->attributes()->from);

        if (isset($stanza->items->item->metadata->info)
         && isset($stanza->items->item->metadata->info->attributes()->url)) {
            $info = $stanza->items->item->metadata->info->attributes();

            $c = \App\Contact::firstOrNew(['id' => $jid]);

            if ($info->id != $c->bannerhash) {
                $c->bannerhash = $info->id;
                $c->save();

                $p = new Image;

                if ($p->fromURL((string)$info->url)) {
                    $p->setKey($jid . '_banner');
                    $p->save();
                }
            }
        }
    }
}
