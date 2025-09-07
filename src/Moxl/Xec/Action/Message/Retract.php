<?php

namespace Moxl\Xec\Action\Message;

use Moxl\Xec\Action;
use Moxl\Stanza\Message;

class Retract extends Action
{
    protected $_to;
    protected $_id;
    protected $_type = 'chat';

    public function request()
    {
        Message::retract($this->_to, $this->_id, $this->_type);
    }
}
