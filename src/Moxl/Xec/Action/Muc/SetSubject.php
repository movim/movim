<?php

namespace Moxl\Xec\Action\Muc;

use App\Message;
use Moxl\Stanza\Muc;
use Moxl\Xec\Action;

class SetSubject extends Action
{
    protected $_to;

    protected $_subject;

    public function request()
    {
        $this->store();
        $this->send(Muc::setSubject(
            linker($this->sessionId)->session->get('id'),
            $this->_to,
            $this->_subject
        ));
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $message = Message::findByStanza($this->me, $stanza);
        $message->set($this->me, $stanza, $parent);

        if (
            ! $message->encrypted
            && (! $message->isEmpty() || $message->isSubject())
        ) {
            $message->save();
            $this->pack($message);
            $this->deliver();
        }
    }
}
