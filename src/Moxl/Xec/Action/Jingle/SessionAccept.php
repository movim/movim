<?php

namespace Moxl\Xec\Action\Jingle;

use Movim\CurrentCall;
use Moxl\Xec\Action;
use Moxl\Stanza\Jingle;

class SessionAccept extends Action
{
    protected $_to;
    protected $_id;

    public function request()
    {
        $this->store();
        Jingle::sessionAccept($this->_id);
        Jingle::sessionProceed($this->_to, $this->_id);
    }
}
