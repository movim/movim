<?php

namespace Moxl\Xec\Payload;

class Carbons extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $parentfrom = current(explode('/', (string)$parent->attributes()->from));
        $message = $stanza->forwarded->message;

        if ($parentfrom == \App\User::me()->id) {
            if ($message->{'apply-to'}
             && $message->{'apply-to'}->attributes()->xmlns == 'urn:xmpp:fasten:0'
             && $message->{'apply-to'}->retract
             && $message->{'apply-to'}->retract->attributes()->xmlns == 'urn:xmpp:message-retract:0') {
                // Another client just retracted a message
                $retracted = new Retracted;
                $retracted->handle($message->{'apply-to'}->retract, $message);
            } elseif ($message->body || $message->subject
            || ($message->reactions && $message->reactions->attributes()->xmlns == 'urn:xmpp:reactions:0')) {
                $m = \App\Message::findByStanza($message);
                $m = $m->set($message, $stanza->forwarded);

                if (!$message->reactions) {
                    $m->save();
                }

                $m = $m->fresh();

                if (!$message->reactions) {
                    $m->clearUnreads();
                }

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
