<?php

namespace Moxl\Xec\Payload;

use Movim\Image;

class BannerMetadata extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $jid = baseJid((string)$parent->attributes()->from);

        if (isset($stanza->items->item->metadata->info)
         && isset($stanza->items->item->metadata->info->attributes()->url)) {
            $p = new Image;

            if ($p->fromURL((string)$stanza->items->item->metadata->info->attributes()->url)) {
                $p->setKey($jid . '_banner');
                $p->save();
            }
        }
    }
}
