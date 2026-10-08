<?php

namespace Moxl\Xec\Payload;

use Moxl\Xec\Action\Jingle\MessageReject;

class JinglePropose extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        // Another session is already started
        if (linker($this->me->session->id)->currentCall->isStarted()) {
            $reject = new MessageReject($this->me, $this->me->session->id);
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
