<?php

namespace Moxl\Xec\Payload;

class Carbons extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $parentfrom = baseJid((string)$parent->attributes()->from);
        $message = $stanza->forwarded->message;

        if ($parentfrom == me()->id) {
            if ($message->retract
             && $message->retract->attributes()->xmlns == 'urn:xmpp:message-retract:1') {
                $retracted = new Retracted;
                $retracted->handle($message->retract, $message);
            } elseif ($message->invite
             && $message->invite->attributes()->xmlns == 'urn:xmpp:call-invites:0') {
                $callInvite = new CallInvitePropose;
                $callInvite->handle($message->invite, $message, carbon: true);
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
            } elseif (count($jingleMessages = $stanza->xpath('//*[@xmlns="urn:xmpp:jingle-message:0"]')) >= 1) {
                $callto = baseJid((string)$message->attributes()->to);

                if ($callto == me()->id || $callto == "") {
                    // We get carbons for calls other clients make as well as calls other clients receive
                    // So make sure we only ring when we see a call _to_ us
                    // Or with no "to", which means from ourselves to ourselves, like another client's <accept>
                    \Moxl\Xec\Handler::handleNode($jingleMessages[0], $message);
                }
            }
        }
    }
}
