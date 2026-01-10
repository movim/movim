<?php

namespace Moxl\Xec\Payload;

use App\Message;
use App\MujiCallParticipant;
use Moxl\Xec\Action\JingleCallInvite\Reject;

class CallInvitePropose extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null, bool $carbon = false)
    {
        // Another session is already started
        if (
            linker($this->me->session->id)->currentCall->isStarted()
            && linker($this->me->session->id)->currentCall->isJidInCall(bareJid($parent->attributes()->from))
        ) {
            $conference = $this->me->session->conferences()->where('conference', \bareJid((string)$parent->attributes()->from))->first();

            if ($conference) {
                // If the propose is from another person
                if (!$conference->presence || $conference->presence->resource != \explodeJid((string)$parent->attributes()->from)['resource']) {
                    $reject = new Reject($this->me, sessionId: $this->sessionId);
                    $reject->setTo(\bareJid((string)$parent->attributes()->from))
                        ->setId((string)$stanza->attributes()->id)
                        ->request();

                    return;
                }
            }
        }

        if (
            $stanza->muji && $stanza->muji->attributes()->xmlns == 'urn:xmpp:jingle:muji:0'
            && $parent->{'stanza-id'} && $parent->{'stanza-id'}->attributes()->xmlns == 'urn:xmpp:sid:0'
        ) {
            $muji = \App\MujiCall::firstOrCreate([
                'id' => (string)$stanza->attributes()->id,
                'session_id' => $this->me->session->id
            ], [
                'muc' => (string)$stanza->muji->attributes()->room,
                'jidfrom' => $carbon
                    ? (string)$parent->attributes()->to
                    : (string)$parent->{'stanza-id'}->attributes()->by,
                'isfromconference' => ((string)$parent->attributes()->type == 'groupchat'),
                'video' => ((string)$stanza->attributes()->video == 'true'),
            ]);

            MujiCallParticipant::firstOrCreate([
                'session_id' => $this->me->session->id,
                'muji_call_id' => (string)$stanza->attributes()->id,
                'jid' => (string)$parent->attributes()->from
            ], [
                'inviter' => true
            ]);

            $message = Message::eventMessageFactory(
                $this->me,
                'muji_propose',
                bareJid((string)$parent->attributes()->from),
                (string)$stanza->attributes()->id
            );
            $message->save();

            $this->pack($message);
            $this->event('muji_message');

            $this->pack($muji);
            $this->deliver();
        }
    }
}
