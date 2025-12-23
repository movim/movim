<?php

namespace Moxl\Xec\Action\Jingle;

use Moxl\Xec\Action;
use Moxl\Stanza\Jingle;

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
