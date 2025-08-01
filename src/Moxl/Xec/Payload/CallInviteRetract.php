<?php

namespace Moxl\Xec\Payload;

use App\Message;
use App\MujiCall;
use Movim\CurrentCall;

class CallInviteRetract extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        if ($parent->{'stanza-id'} && $parent->{'stanza-id'}->attributes()->xmlns == 'urn:xmpp:sid:0') {
            $muji = me()->session->mujiCalls()->where('id', (string)$stanza->attributes()->id)->first();

            if ($muji) {
                $participant = $muji->participants->firstWhere('jid', $parent->attributes()->from);

                if ($participant && $participant->inviter) {
                    $message = Message::eventMessageFactory(
                        'muji_retract',
                        baseJid((string)$parent->attributes()->from),
                        (string)$stanza->attributes()->id
                    );
                    $message->save();

                    $this->pack($message);
                    $this->event('muji_message');

                    CurrentCall::getInstance()->stop(baseJid((string)$parent->attributes()->from), $muji->id);
                    MujiCall::where('id', $muji->id)->where('session_id', $muji->session_id)->delete();

                    $this->pack($muji);
                    $this->deliver();
                }
            }
        }
    }
}
