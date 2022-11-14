<?php

namespace Moxl\Xec\Payload;

use Movim\Image;

class BannerMetadata extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $jid = baseJid((string)$parent->attributes()->from);

        $c = \App\Contact::firstOrNew(['id' => $jid]);

        if (isset($stanza->items->item->metadata->info)
         && isset($stanza->items->item->metadata->info->attributes()->url)) {
            $info = $stanza->items->item->metadata->info->attributes();

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
