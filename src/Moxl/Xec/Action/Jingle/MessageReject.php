<?php

namespace Moxl\Xec\Action\Jingle;

use App\Message;
use Moxl\Xec\Action;
use Moxl\Stanza\Jingle;

class MessageReject extends Action
{
    protected $_to;
    protected $_id;

    public function request()
    {
        $this->store();
        Jingle::messageReject($this->_id, $this->_to);

        $message = Message::eventMessageFactory(
            'jingle',
            baseJid($this->_to),
            (string)$this->_id
        );
        $message->type = 'jingle_reject';
        $message->save();

        $this->pack($message);
        $this->event('jingle_message');
    }
}
