<?php

namespace Moxl\Xec\Payload;

use App\MujiCallParticipant;

class CallInviteAccept extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        if ($parent->{'stanza-id'} && $parent->{'stanza-id'}->attributes()->xmlns == 'urn:xmpp:sid:0') {
            $muji = me()->session->mujiCalls()->where('id', (string)$stanza->attributes()->id)->first();

            if ($muji) {
                MujiCallParticipant::firstOrCreate([
                    'session_id' => SESSION_ID,
                    'muji_call_id' => (string)$stanza->attributes()->id,
                    'jid' => (string)$parent->attributes()->from,
                ]);

                $this->pack($muji);
                $this->deliver();
            }
        }
    }
}
