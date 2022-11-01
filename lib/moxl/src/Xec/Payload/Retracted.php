<?php

namespace Moxl\Xec\Payload;

class Retracted extends Payload
{
    public function handle($stanza, $parent = false)
    {
        if ($parent->{'apply-to'} && $parent->{'apply-to'}->attributes()->xmlns == 'urn:xmpp:fasten:0') {
            $message = \App\User::me()->messages()
                                      ->where('originid', (string)$parent->{'apply-to'}->attributes()->id)
                                      ->where('jidfrom', baseJid((string)$parent->attributes()->from))
                                      ->first();

            // Only retract one-to-one messages for now
            if ($message && !$message->isMuc()) {
                $message->retract();
                $message->save();

                $this->pack($message);
                $this->deliver();
            }
        }
    }
}
