<?php

namespace Moxl\Xec\Payload;

use Moxl\Stanza\Avatar;
use React\Http\Message\Response;

class AvatarData extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $jid = baseJid((string)$parent->attributes()->from);

        requestAvatarBase64(
            jid: $jid,
            base64: (string)$stanza->items->item->data,
            type: Avatar::NODE_DATA
        )->then(
            function (Response $response) use ($jid) {
                $this->pack($jid);
                $this->event('vcard');
            }
        );
    }
}
