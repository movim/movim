<?php

namespace Moxl\Xec\Payload;

use Psr\Http\Message\ResponseInterface;

class BannerMetadata extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $jid = baseJid((string)$parent->attributes()->from);

        if (
            isset($stanza->items->item->metadata->info)
            && isset($stanza->items->item->metadata->info->attributes()->url)
        ) {
            requestAvatarUrl(
                jid: $jid,
                url: (string)$stanza->items->item->metadata->info->attributes()->url,
                banner: true
            )->then(function (ResponseInterface $response) use ($jid) {
                $this->deliver();
            });
        }
    }
}
