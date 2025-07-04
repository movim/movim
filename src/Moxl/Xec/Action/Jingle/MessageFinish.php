<?php

namespace Moxl\Xec\Action\Jingle;

use Moxl\Xec\Action;
use Moxl\Stanza\Jingle;

class MessageFinish extends Action
{
    protected $_to;
    protected $_id;
    protected $_reason;

    public function request()
    {
        $this->store();
        Jingle::messageFinish($this->_to, $this->_id, $this->_reason);
    }
}
