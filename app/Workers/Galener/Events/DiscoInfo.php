<?php

namespace App\Workers\Galener\Events;

use Moxl\Stanza\Disco;

class DiscoInfo extends Event
{
    public static function getHandlerPath(): string
    {
        return 'iq|query{http://jabber.org/protocol/disco#info}';
    }

    public function handle(): ?\DOMDocument
    {
        if ($this->node->type == 'get') {
            return $this->iq(type: 'result', xml: Disco::answerGalener());
        }

        return null;
    }
}
