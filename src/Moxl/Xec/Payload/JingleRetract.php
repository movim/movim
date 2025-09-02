<?php

namespace Moxl\Xec\Payload;

use App\Message;

class JingleRetract extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $from = (string)$parent->attributes()->from;

        if (!$stanza->muji) {
            $message = Message::eventMessageFactory(
                'jingle',
                baseJid($from),
                (string)$stanza->attributes()->id
            );
            $message->type = 'jingle_retract';
            $message->save();

            $this->pack($message);
            $this->event('jingle_message');
        }

        $this->pack((string)$stanza->attributes()->id, $from);
        $this->deliver();
    }
}
