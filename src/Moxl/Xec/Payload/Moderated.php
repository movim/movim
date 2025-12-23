<?php

namespace Moxl\Xec\Payload;

class Moderated extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        if ($parent->{'apply-to'} && $parent->{'apply-to'}->attributes()->xmlns == 'urn:xmpp:fasten:0') {
            $message = $this->me->messages()
                                      ->where('stanzaid', (string)$parent->{'apply-to'}->attributes()->id)
                                      ->where('jidfrom', bareJid((string)$parent->attributes()->from))
                                      ->first();

            if ($message && $message->isMuc()) {
                $message->retract();
                $message->save();

                $this->pack($message);
                $this->deliver();
            }
        }
    }
}
