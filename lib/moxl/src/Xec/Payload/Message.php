<?php

namespace Moxl\Xec\Payload;

use App\BundleCapabilityResolver;
use Movim\ChatStates;

class Message extends Payload
{
    public function handle($stanza, $parent = false)
    {
        if ($stanza->confirm
        && $stanza->confirm->attributes()->xmlns == 'http://jabber.org/protocol/http-auth') {
            return;
        }

        // Retracted messages are handled by Retracted
        if ($stanza->{'apply-to'}
        && $stanza->{'apply-to'}->attributes()->xmlns == 'urn:xmpp:fasten:0'
        && $stanza->{'apply-to'}->retract
        && $stanza->{'apply-to'}->retract->attributes()->xmlns == 'urn:xmpp:message-retract:0') {
            return;
        }

        if ($stanza->attributes()->type == 'error') {
            return;
        }

        $message = \App\Message::findByStanza($stanza);
        $message = $message->set($stanza, $parent);

        if ($message->type == 'chat' && \App\User::me()->hasBlocked($message->jidfrom)) {
            return;
        }

        if ($stanza->composing || $stanza->paused || $stanza->active) {
            $from = ($message->type == 'groupchat')
                ? $message->jidfrom.'/'.$message->resource
                : $message->jidfrom;

            if ($stanza->composing) {
                (ChatStates::getInstance())->composing($from, $message->jidto, isset($message->mucpm));
            }

            if ($stanza->paused || $stanza->active) {
                (ChatStates::getInstance())->paused($from, $message->jidto, isset($message->mucpm));
            }
        }

        if ($message->valid()
        && (!$message->isEmpty() || $message->isSubject())) {
            $message->save();
            $message = $message->fresh();

            if ($message->bundleid) {
                BundleCapabilityResolver::getInstance()->resolve($message);
            }

            if ($message && ($message->body || $message->subject)) {
                $this->pack($message);

                if ($message->subject && $message->type == 'groupchat') {
                    $this->event('subject');
                }

                $this->deliver();
            }
        }
    }
}
