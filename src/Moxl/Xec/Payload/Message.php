<?php

namespace Moxl\Xec\Payload;

use Moxl\Xec\Action\Muc\GetConfig;

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

        if ($message->isMuc() && linker($this->sessionId)->chatroomPings->has($message->jidfrom)) {
            linker($this->sessionId)->chatroomPings->touch($message->jidfrom);
        }

        /**
         * Muc events
         */
        if (
            $stanza->x
            && $stanza->x->attributes()->xmlns == 'http://jabber.org/protocol/muc#user'
            && $stanza->x->status
            && $stanza->x->status->attributes()->code == '104'
        ) {
            $getConfig = new GetConfig($this->me, $this->sessionId);
            $getConfig->setTo((string)$stanza->attributes()->from)
                ->request();
            return;
        }

        /**
         * Pubsub events
         */

        if (
            $stanza->event
            && $stanza->event->attributes()->xmlns == 'http://jabber.org/protocol/pubsub#event'
        ) {
            $from = (string)$stanza->attributes()->from;
            if (
                $stanza->event->subscription
                && $stanza->event->subscription->attributes()->subscription == 'subscribed'
            ) {
                $this->pack([
                    'server' => $from,
                    'node' => (string)$stanza->event->subscription->attributes()->node
                ]);
                $this->event('message_pubsub_subscribed');
            } elseif (
                $stanza->event->configuration
                && isset($stanza->event->configuration->x)
                && (string)$stanza->event->configuration->x->attributes()->xmlns == 'jabber:x:data'
            ) {
                $node = $stanza->event->configuration->attributes()->node;
                $info = \App\Info::where('server', $from)->where('node', $node)->first();

                if ($info) {
                    $info->setXForm($stanza->configuration->x);
                    $info->save();

                    $this->pack([
                        'server' => $from,
                        'node' => $node
                    ]);
                    $this->event('message_pubsub_configuration');
                }
            }

            return;
        }

        if ($stanza->composing || $stanza->paused || $stanza->active) {
            $from = ($message->isMuc())
                ? $message->jidfrom . '/' . $message->resource
                : $message->jidfrom;

            if ($stanza->composing) {
                linker($this->sessionId)->chatStates->composing($from, $message->jidto, isset($message->mucpm));
            }

            if ($stanza->paused || $stanza->active) {
                linker($this->sessionId)->chatStates->paused($from, $message->jidto, isset($message->mucpm));
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
