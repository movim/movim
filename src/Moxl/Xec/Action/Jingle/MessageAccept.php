<?php

namespace Moxl\Xec\Action\Jingle;

use Moxl\Xec\Action;
use Moxl\Stanza\Jingle;

class MessageAccept extends Action
{
    protected $_id;

    public function request()
    {
        $this->store();
        Jingle::messageAccept($this->_id);
    }
}
