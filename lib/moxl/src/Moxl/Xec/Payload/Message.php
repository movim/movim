<?php

namespace Moxl\Xec\Payload;

use Movim\ChatStates;

class Message extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $from = (string)$stanza->attributes()->type == 'groupchat'
            ? (string)$stanza->attributes()->from
            : current(explode('/', (string)$stanza->attributes()->from));
        $to = current(explode('/', (string)$stanza->attributes()->to));

        if ($stanza->confirm
        && $stanza->confirm->attributes()->xmlns == 'http://jabber.org/protocol/http-auth') {
            return;
        }

        if ($stanza->attributes()->type == 'error') {
            return;
        }

        if ($stanza->composing) {
            (ChatStates::getInstance())->composing($from, $to);
        }

        if ($stanza->paused) {
            (ChatStates::getInstance())->paused($from, $to);
        }

        $message = \App\Message::findByStanza($stanza);
        $message->set($stanza, $parent);

        if (!$message->isOTR()
        && (!$message->isEmpty() || $message->isSubject())) {
            $message->save();

            if ($message->body || $message->subject) {
                $this->pack($message);
                $this->deliver();
            }
        }
    }
}
