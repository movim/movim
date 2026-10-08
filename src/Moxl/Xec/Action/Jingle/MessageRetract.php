<?php

namespace Moxl\Xec\Action\Jingle;

use Moxl\Stanza\Jingle;
use Moxl\Xec\Action;

class MessageRetract extends Action
{
    protected $_to;

    protected $_id;

    public function request()
    {
        $this->store();
        $this->send(Jingle::messageRetract($this->_to, $this->_id));
    }
}
