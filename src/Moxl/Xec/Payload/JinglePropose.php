<?php

namespace Moxl\Xec\Payload;

use Movim\CurrentCall;
use Moxl\Xec\Action\Jingle\MessageReject;

class JinglePropose extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        // Another session is already started
        if (CurrentCall::getInstance()->isStarted()) {
            $reject = new MessageReject($this->me);
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
            'id' => (string)$stanza->attributes()->id,
            'withVideo' => $withVideo
        ], (string)$parent->attributes()->from);

        $this->deliver();
    }
}
