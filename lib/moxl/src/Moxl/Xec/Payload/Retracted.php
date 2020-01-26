<?php

namespace Moxl\Xec\Payload;

class Retracted extends Payload
{
    public function handle($stanza, $parent = false)
    {
        if ($parent->{'apply-to'} && $parent->{'apply-to'}->attributes()->xmlns == 'urn:xmpp:fasten:0') {
            $message = \App\User::me()->messages()
                                      ->where('originid', (string)$parent->{'apply-to'}->attributes()->id)
                                      ->where('jidfrom', current(explode('/', (string)$parent->attributes()->from)))
                                      ->first();

            if ($message) {
                $message->retract();
                $message->save();

                $this->pack($message);
                $this->deliver();
            }
        }
    }
}
