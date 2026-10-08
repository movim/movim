<?php

namespace Moxl\Xec\Action\Jingle;

use Moxl\Stanza\Jingle;
use Moxl\Xec\Action;

class MessageRinging extends Action
{
    protected $_to;

    protected $_id;

    public function request()
    {
        $this->store();
        $this->send(Jingle::messageRinging($this->_to, $this->_id));
    }
}
