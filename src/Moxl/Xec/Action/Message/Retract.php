<?php

namespace Moxl\Xec\Action\Message;

use Moxl\Stanza\Message;
use Moxl\Xec\Action;

class Retract extends Action
{
    protected $_to;

    protected $_id;

    protected $_type = 'chat';

    public function request()
    {
        $this->send(Message::retract($this->_to, $this->_id, $this->_type));
    }
}
