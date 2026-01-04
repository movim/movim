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
        $this->send(Jingle::messageReject($this->_id, $this->_to));

        $message = Message::eventMessageFactory(
            $this->me,
            'jingle',
            bareJid($this->_to),
            (string)$this->_id
        );
        $message->type = 'jingle_reject';
        $message->save();

        $this->pack($message);
        $this->event('jingle_message');
    }
}
