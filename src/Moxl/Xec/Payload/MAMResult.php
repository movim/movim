<?php

namespace Moxl\Xec\Payload;

//use App\MessageBuffer;

class MAMResult extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $to = bareJid((string)$parent->attributes()->to);
        $session = linker($this->sessionId)->session;

        $messagesCounter = $session->get('mamid' . (string)$stanza->attributes()->queryid);

        if (
            $stanza->forwarded->delay
            && isset($stanza->attributes()->queryid)
            && $messagesCounter >= 0
        ) {
            $session->set('mamid' . (string)$stanza->attributes()->queryid, $messagesCounter + 1);

            $message = \App\Message::findByStanza($this->me, $stanza, $parent);
            $message = $message->set($this->me, $stanza->forwarded->message, $stanza->forwarded);

            // parent message doesn't exists
            if ($message == null) {
                return;
            }

            if (
                $message->published && strtotime($message->published) > mktime(0, 0, 0, gmdate("m"), gmdate("d") - 3, gmdate("Y"))
            ) {
                if (
                    $stanza->forwarded->message->retract
                    && $stanza->forwarded->message->retract->attributes()->xmlns == 'urn:xmpp:message-retract:1'
                ) {
                    $retracted = new Retracted($this->me, sessionId: $this->sessionId);
                    $retracted->handle($stanza->forwarded->message->retract, $stanza->forwarded->message);
                    return;
                }

                if (
                    $stanza->forwarded->message->invite
                    && $stanza->forwarded->message->invite->attributes()->xmlns == 'urn:xmpp:call-invites:0'
                ) {
                    $invite = new CallInvitePropose($this->me, sessionId: $this->sessionId);
                    $invite->handle($stanza->forwarded->message->invite, $stanza->forwarded->message);
                }

                if (
                    $stanza->forwarded->message->retract
                    && $stanza->forwarded->message->retract->attributes()->xmlns == 'urn:xmpp:call-invites:0'
                ) {
                    $retract = new CallInviteRetract($this->me, sessionId: $this->sessionId);
                    $retract->handle($stanza->forwarded->message->retract, $stanza->forwarded->message);
                }

                if (
                    $stanza->forwarded->message->accept
                    && $stanza->forwarded->message->accept->attributes()->xmlns == 'urn:xmpp:call-invites:0'
                ) {
                    $accept = new CallInviteAccept($this->me, sessionId: $this->sessionId);
                    $accept->handle($stanza->forwarded->message->accept, $stanza->forwarded->message);
                }

                if (
                    $stanza->forwarded->message->left
                    && $stanza->forwarded->message->left->attributes()->xmlns == 'urn:xmpp:call-invites:0'
                ) {
                    $left = new CallInviteLeft($this->me, sessionId: $this->sessionId);
                    $left->handle($stanza->forwarded->message->left, $stanza->forwarded->message);
                }
            }

            if ($message->isMuc()) {
                $message->jidfrom = bareJid(($message->jidfrom));
            }

            if (!empty($to) && empty($message->jidto)) {
                $message->jidto = $to;
            }

            if (
                !$message->encrypted
                && $message->valid()
                && (!$message->isEmpty() || $message->isSubject())
            ) {
                // Set the "old" message as seen
                if (\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $message->published)->addWeek()->isBefore(\Carbon\Carbon::now())) {
                    $message->seen = true;
                }

                //MessageBuffer::getInstance()->append($message, function() use ($message) {
                $message->save();
                $message->clearUnreads();

                $this->pack($message);
                $this->deliver();
                //});
            }
        }
    }
}
