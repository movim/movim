<?php

namespace Moxl\Xec\Payload;

use App\MujiCallParticipant;

class CallInvitePropose extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        if ($stanza->muji && $stanza->muji->attributes()->xmlns == 'urn:xmpp:jingle:muji:0'
        && $parent->{'stanza-id'} && $parent->{'stanza-id'}->attributes()->xmlns == 'urn:xmpp:sid:0') {
            $muji = \App\MujiCall::firstOrCreate([
                'id' => (string)$stanza->attributes()->id,
                'muc' => (string)$stanza->muji->attributes()->room,
                'conference_id' => (string)$parent->{'stanza-id'}->attributes()->by,
                'video' => (bool)$stanza->attributes()->video,
            ]);

            MujiCallParticipant::firstOrCreate([
                'muji_call_id' => (string)$stanza->attributes()->id,
                'jid' => (string)$parent->attributes()->from,
            ]);

            $this->pack($muji);
            $this->deliver();
        }
    }
}
