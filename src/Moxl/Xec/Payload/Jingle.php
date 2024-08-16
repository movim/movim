<?php

namespace Moxl\Xec\Payload;

use App\Message as Message;
use Movim\CurrentCall;
use Moxl\Stanza\Ack;
use Moxl\Stanza\Jingle as JingleStanza;

class Jingle extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $from = (string)$parent->attributes()->from;
        $id   = (string)$parent->attributes()->id;

        $action = (string)$stanza->attributes()->action;

        $message = Message::eventMessageFactory(
            'jingle',
            baseJid((string)$from),
            (string)$stanza->attributes()->sid
        );

        $sid = CurrentCall::getInstance()->id;

        if ($sid == $message->thread) {
            Ack::send($from, $id);

            switch ($action) {
                case 'session-initiate':
                    $message->type = 'jingle_incoming';
                    $message->save();

                    $stanza = CurrentCall::getInstance()->setContent($stanza);

                    $this->event('jingle_sessioninitiate', [$stanza, $from]);

                    $this->pack($message);
                    $this->event('jingle_message');
                    break;
                case 'session-info':
                    if ($stanza->mute) {
                        $this->event('jingle_sessionmute', 'mid' . (string)$stanza->mute->attributes()->name);
                    }
                    if ($stanza->unmute) {
                        $this->event('jingle_sessionunmute', 'mid' . (string)$stanza->unmute->attributes()->name);
                    }
                    break;
                case 'transport-info':
                    $this->event('jingle_transportinfo', $stanza);
                    break;
                case 'session-terminate':
                    $message->type = 'jingle_end';
                    $message->save();
                    $this->event('jingle_sessionterminate', (string)$stanza->reason->children()[0]->getName());

                    $this->pack($message);
                    $this->event('jingle_message');
                    break;
                case 'session-accept':
                    $message->type = 'jingle_outgoing';
                    $message->save();
                    $this->event('jingle_sessionaccept', $stanza);

                    $this->pack($message);
                    $this->event('jingle_message');
                    break;
                case 'content-add':
                    $stanza = CurrentCall::getInstance()->setContent($stanza);
                    $this->event('jingle_contentadd', $stanza);
                    break;
                case 'content-modify':
                    $stanza = CurrentCall::getInstance()->setContent($stanza);
                    $this->event('jingle_contentmodify', $stanza);
                    break;
                case 'content-remove':
                    $stanza = CurrentCall::getInstance()->setContent($stanza);
                    $this->event('jingle_contentremove', $stanza);
                    break;
            }
        } else {
            JingleStanza::unknownSession($from, $id);
        }
    }
}
