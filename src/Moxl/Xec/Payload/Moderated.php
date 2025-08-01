<?php

namespace Moxl\Xec\Payload;

class Moderated extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        if ($parent->{'apply-to'} && $parent->{'apply-to'}->attributes()->xmlns == 'urn:xmpp:fasten:0') {
            $message = me()->messages()
                                      ->where('stanzaid', (string)$parent->{'apply-to'}->attributes()->id)
                                      ->where('jidfrom', baseJid((string)$parent->attributes()->from))
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
