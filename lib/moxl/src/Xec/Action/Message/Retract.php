<?php

namespace Moxl\Xec\Action\Message;

use Moxl\Xec\Action;
use Moxl\Stanza\Message;

class Retract extends Action
{
    protected $_to;
    protected $_originid;

    public function request()
    {
        Message::retract($this->_to, $this->_originid);
    }
}
