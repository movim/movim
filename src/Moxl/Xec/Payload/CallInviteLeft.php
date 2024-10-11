<?php

namespace Moxl\Xec\Payload;

use Carbon\Carbon;

class CallInviteLeft extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        if ($parent->{'stanza-id'} && $parent->{'stanza-id'}->attributes()->xmlns == 'urn:xmpp:sid:0') {
            $muji = \App\User::me()->session->mujiCalls()->where('id', (string)$stanza->attributes()->id)->first();

            if ($muji) {
                $participant = $muji->participants->firstWhere('jid', (string)$parent->attributes()->from);
                $participant->left_at = Carbon::now();
                $participant->save();

                $this->pack($muji);
                $this->deliver();
            }

        }
    }
}
