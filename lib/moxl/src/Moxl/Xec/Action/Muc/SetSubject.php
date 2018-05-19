<?php

namespace Moxl\Xec\Action\Muc;

use Moxl\Xec\Action;
use Moxl\Stanza\Muc;

class SetSubject extends Action
{
    private $_to;
    private $_subject;

    public function request()
    {
        $this->store();
        Muc::setSubject($this->_to, $this->_subject);
    }

    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }

    public function setSubject($subject)
    {
        $this->_subject = $subject;
        return $this;
    }

    public function handle($stanza, $parent = false)
    {
        $message = \App\Message::findByStanza($stanza);
        $message->set($stanza, $parent);

        if (!$message->isOTR()
        && (!$message->isEmpty() || $message->isSubject())) {
            $message->save();
            $this->pack($message);
            $this->deliver();
        }
    }
}
