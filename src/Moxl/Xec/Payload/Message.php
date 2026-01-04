<?php

namespace Moxl\Xec\Payload;

use Movim\ChatroomPings;
use Movim\ChatStates;

class Message extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        if (
            $stanza->confirm
            && $stanza->confirm->attributes()->xmlns == 'http://jabber.org/protocol/http-auth'
        ) {
            return;
        }

        // Retracted messages are handled by Retracted
        if (
            $stanza->retract
            && $stanza->retract->attributes()->xmlns == 'urn:xmpp:message-retract:1'
        ) {
            return;
        }

        if ($stanza->attributes()->type == 'error') {
            return;
        }

        $message = \App\Message::findByStanza($this->me, $stanza);
        $message = $message->set($this->me, $stanza, $parent);

        // parent message doesn't exists
        if ($message == null) {
            return;
        }

        if ($message->type == 'chat' && $this->me?->hasBlocked($message->jidfrom)) {
            return;
        }

        if ($message->isMuc() && ChatroomPings::getInstance($this->me)->has($message->jidfrom)) {
            ChatroomPings::getInstance($this->me)->touch($message->jidfrom);
        }

        if ($stanza->composing || $stanza->paused || $stanza->active) {
            $from = ($message->isMuc())
                ? $message->jidfrom . '/' . $message->resource
                : $message->jidfrom;

            if ($stanza->composing) {
                (ChatStates::getInstance($this->me))->composing($from, $message->jidto, isset($message->mucpm));
            }

            if ($stanza->paused || $stanza->active) {
                (ChatStates::getInstance($this->me))->paused($from, $message->jidto, isset($message->mucpm));
            }
        }

        if (
            $message->valid()
            && (!$message->isEmpty() || $message->isSubject())
        ) {
            $message->save();
            $message = $message->fresh();

            if ($message && ($message->body || $message->subject)) {
                $this->pack($message);

                if ($message->subject && $message->isMuc()) {
                    $this->event('subject');
                }

                $this->deliver();
            }
        }
    }
}
