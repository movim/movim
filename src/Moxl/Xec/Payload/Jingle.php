<?php

namespace Moxl\Xec\Payload;

use App\Message as Message;
use Moxl\Stanza\Jingle as JingleStanza;

class Jingle extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $from = (string)$parent->attributes()->from;
        $id   = (string)$parent->attributes()->id;

        $action = (string)$stanza->attributes()->action;

        $message = Message::eventMessageFactory(
            $this->me,
            'jingle',
            bareJid((string)$from),
            (string)$stanza->attributes()->sid
        );

        //if ($linkerManager->currentCall($this->me->session->id)->hasId($message->thread)) {
            $this->iq(to: $from, id: $id, type: 'result');

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
                    if (!$stanza->muji && linker($this->me->session->id)->currentCall->hasId($stanza->attributes()->sid)) {
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
