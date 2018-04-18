<?php

namespace Moxl\Xec\Payload;

class Message extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $jid = explode('/',(string)$stanza->attributes()->from);
        $to = current(explode('/',(string)$stanza->attributes()->to));

        if ($stanza->confirm
        && $stanza->confirm->attributes()->xmlns == 'http://jabber.org/protocol/http-auth') {
            return;
        }

        if ($stanza->composing)
            $this->event('composing', [$jid[0], $to]);
        if ($stanza->paused)
            $this->event('paused', [$jid[0], $to]);
        if ($stanza->gone)
            $this->event('gone', [$jid[0], $to]);

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
