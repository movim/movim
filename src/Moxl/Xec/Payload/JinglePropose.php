<?php

namespace Moxl\Xec\Payload;

use Movim\CurrentCalls;
use Moxl\Xec\Action\Jingle\SessionReject;

class JinglePropose extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        // Another session is already started
        if (CurrentCalls::getInstance()->isStarted()) {
            $reject = new SessionReject;
            $reject->setTo((string)$parent->attributes()->from)
                   ->setId((string)$stanza->attributes()->id)
                   ->request();
            return;
        }

        $withVideo = false;
        foreach ($stanza->xpath('//description/@media') as $attribute) {
            if ((string)$attribute == 'video') $withVideo = true;
        }

        $this->pack([
            'from' => (string)$parent->attributes()->from,
            'id' => (string)$stanza->attributes()->id,
            'withVideo' => $withVideo
        ]);

        $this->deliver();
    }
}
