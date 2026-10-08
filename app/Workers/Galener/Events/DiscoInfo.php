<?php

namespace App\Workers\Galener\Events;

use Moxl\Stanza\Disco;
use Moxl\Utils;

class DiscoInfo extends Event
{
    public static function getHandlerPaths(): array
    {
        return [
            'iq|query{http://jabber.org/protocol/disco#info}@urn:xmpp:caps#' . Utils::CAPABILITY_HASH_ALGORITHM . '.' . Utils::getOwnGalenerCapabilityHash(),
            'iq|query{http://jabber.org/protocol/disco#info}'
        ];
    }

    public function handle(): ?\DOMDocument
    {
        if ($this->node->type == 'get') {
            return $this->iq(type: 'result', xml: Disco::answerGalener());
        }

        return null;
    }
}
