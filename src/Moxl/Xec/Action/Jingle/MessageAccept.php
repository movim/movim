<?php

namespace Moxl\Xec\Action\Jingle;

use Moxl\Stanza\Jingle;
use Moxl\Xec\Action;

class MessageAccept extends Action
{
    protected $_id;

    public function request()
    {
        $this->store();
        $this->send(Jingle::messageAccept($this->_id));
    }
}
