<?php

namespace Moxl\Xec\Payload;

use App\Message as Message;
use Movim\CurrentCalls;
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

        if (CurrentCalls::getInstance()->hasId($message->thread)) {
            Ack::send($from, $id);

            switch ($action) {
                case 'session-initiate':
                    $message->type = 'jingle_incoming';
                    $message->save();

                    $stanza = CurrentCalls::getInstance()->setContent($stanza);

                    $this->pack($stanza, $from);
                    $this->event('jingle_sessioninitiate');

                    $this->pack($message);
                    $this->event('jingle_message');
                    break;
                case 'session-info':
                    if ($stanza->mute) {
                        $this->pack('mid' . (string)$stanza->mute->attributes()->name, $from);
                        $this->event('jingle_sessionmute');
                    }
                    if ($stanza->unmute) {
                        $this->pack('mid' .  (string)$stanza->unmute->attributes()->name, $from);
                        $this->event('jingle_sessionunmute');
                    }
                    break;
                case 'transport-info':
                    $this->pack($stanza, $from);
                    $this->event('jingle_transportinfo');
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

                    $this->pack($stanza, $from);
                    $this->event('jingle_sessionaccept');

                    $this->pack($message);
                    $this->event('jingle_message');
                    break;
                case 'content-add':
                    $stanza = CurrentCalls::getInstance()->setContent($stanza);
                    $this->pack($stanza, $from);
                    $this->event('jingle_contentadd');
                    break;
                case 'content-modify':
                    $stanza = CurrentCalls::getInstance()->setContent($stanza);
                    $this->pack($stanza, $from);
                    $this->event('jingle_contentmodify');
                    break;
                case 'content-remove':
                    $stanza = CurrentCalls::getInstance()->setContent($stanza);
                    $this->pack($stanza, $from);
                    $this->event('jingle_contentremove');
                    break;
            }
        } else {
            JingleStanza::unknownSession($from, $id);
        }
    }
}
