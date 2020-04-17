<?php

namespace Moxl\Xec\Payload;

class JinglePropose extends Payload
{
    public function handle($stanza, $parent = false)
    {
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
