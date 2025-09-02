<?php

namespace Moxl\Xec\Payload;

use App\Message;
use Movim\CurrentCall;

class JingleFinish extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        // We can only reject the current session
        if (!CurrentCall::getInstance()->hasId((string)$stanza->attributes()->id)) return;

        $from = (string)$parent->attributes()->from;

        if (!$stanza->muji) {
            $message = Message::eventMessageFactory(
                'jingle',
                baseJid($from),
                (string)$stanza->attributes()->id
            );
            $message->type = 'jingle_finish';
            $message->save();

            $this->pack($message);
            $this->event('jingle_message');
        }

        $this->pack((string)$stanza->attributes()->id, (string)$parent->attributes()->from);
        $this->deliver();
    }
}
