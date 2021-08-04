<?php

namespace Moxl\Xec\Payload;

use Moxl\Xec\Action\Jingle\SessionReject;
use Movim\Session;

class JinglePropose extends Payload
{
    public function handle($stanza, $parent = false)
    {
        // Another session is already started
        if (Session::start()->get('jingleSid')) {
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
