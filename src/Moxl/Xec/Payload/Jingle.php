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

        //if (CurrentCall::getInstance()->hasId($message->thread)) {
            Ack::send($from, $id);

            switch ($action) {
                case 'session-initiate':
                    $this->pack($stanza, $from);
                    $this->event('jingle_sessioninitiate');
                    break;
                case 'session-info':
                    if ($stanza->mute) {
                        $this->pack('mid' . (string)$stanza->mute->attributes()->name, $from);
                        $this->event('jingle_sessionmute');
                    }
                    if ($stanza->unmute) {
                        $this->pack('mid' . (string)$stanza->unmute->attributes()->name, $from);
                        $this->event('jingle_sessionunmute');
                    }
                    break;
                case 'transport-info':
                    $this->pack($stanza, $from);
                    $this->event('jingle_transportinfo');
                    break;
                case 'session-terminate':
                    if (!$stanza->muji && CurrentCall::getInstance()->hasId($stanza->attributes()->sid)) {
                        $message->type = 'jingle_end';
                        $message->save();

                        $this->pack($message);
                        $this->event('jingle_message');
                    }

                    $this->pack((string)$stanza->attributes()->sid, $from);
                    $this->event('jingle_sessionterminate');
                    break;
                case 'session-accept':
                    $this->pack($stanza, $from);
                    $this->event('jingle_sessionaccept');
                    break;
                case 'content-add':
                    $this->pack($stanza, $from);
                    $this->event('jingle_contentadd');
                    break;
                case 'content-modify':
                    $this->pack($stanza, $from);
                    $this->event('jingle_contentmodify');
                    break;
                case 'content-remove':
                    $this->pack($stanza, $from);
                    $this->event('jingle_contentremove');
                    break;
            }
        /*} else {
            JingleStanza::unknownSession($from, $id);
        }*/
    }
}
