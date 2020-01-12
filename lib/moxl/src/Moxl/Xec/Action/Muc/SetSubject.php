<?php

namespace Moxl\Xec\Action\Muc;

use Moxl\Xec\Action;
use Moxl\Stanza\Muc;

class SetSubject extends Action
{
    protected $_to;
    protected $_subject;

    public function request()
    {
        $this->store();
        Muc::setSubject($this->_to, $this->_subject);
    }

    public function handle($stanza, $parent = false)
    {
        $message = \App\Message::findByStanza($stanza);
        $message->set($stanza, $parent);

        if (!$message->encrypted
        && (!$message->isEmpty() || $message->isSubject())) {
            $message->save();
            $this->pack($message);
            $this->deliver();
        }
    }
}
