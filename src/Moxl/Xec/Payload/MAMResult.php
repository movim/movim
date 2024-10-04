<?php

namespace Moxl\Xec\Payload;

use Movim\Session;
//use App\MessageBuffer;

class MAMResult extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $to = baseJid((string)$parent->attributes()->to);
        $session = Session::instance();

        $messagesCounter = $session->get('mamid' . (string)$stanza->attributes()->queryid);

        if (
            $stanza->forwarded->delay
            && isset($stanza->attributes()->queryid)
            && $messagesCounter >= 0
        ) {
            $session->set('mamid' . (string)$stanza->attributes()->queryid, $messagesCounter + 1);

            if ($stanza->forwarded->message->retract
             && $stanza->forwarded->message->retract->attributes()->xmlns == 'urn:xmpp:message-retract:1') {
                $retracted = new Retracted;
                $retracted->handle($stanza->forwarded->message->retract, $stanza->forwarded->message);
            }

            $message = \App\Message::findByStanza($stanza, $parent);
            $message = $message->set($stanza->forwarded->message, $stanza->forwarded);

            // parent message doesn't exists
            if ($message == null) {
                return;
            }

            if ($message->isMuc()) {
                $message->jidfrom = baseJid(($message->jidfrom));
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
