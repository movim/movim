<?php

namespace Moxl\Xec\Payload;

class Carbons extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $parentfrom = current(explode('/', (string)$parent->attributes()->from));
        $message = $stanza->forwarded->message;

        if ($parentfrom == \App\User::me()->id) {
            if ($message->body || $message->subject) {
                $m = \App\Message::findByStanza($message);
                $m->set($message, $stanza->forwarded);

                $m->save();
                $m = $m->fresh();

                $m->clearUnreads();

                $this->pack($m);
                $this->deliver();
            } elseif ($message->displayed) {
                // Another client just displayed the message
                $displayed = new Displayed;
                $displayed->handle($message->displayed, $message);
            } elseif (count($jingle_messages = $stanza->xpath('//*[@xmlns="urn:xmpp:jingle-message:0"]')) >= 1) {
                $callto = current(explode('/', (string)$message->attributes()->to));
                if ($callto == \App\User::me()->id || $callto == "") {
                    // We get carbons for calls other clients make as well as calls other clients receive
                    // So make sure we only ring when we see a call _to_ us
                    // Or with no "to", which means from ourselves to ourselves, like another client's <accept>
                    \Moxl\Xec\Handler::handleNode($jingle_messages[0], $message);
                }
            }
        }
    }
}
