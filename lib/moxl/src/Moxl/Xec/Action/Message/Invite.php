<?php

namespace Moxl\Xec\Action\Message;

use Moxl\Xec\Action;
use Moxl\Stanza\Message;

class Invite extends Action
{
    protected $_to;
    protected $_content;
    protected $_id;
    protected $_invite;

    public function request()
    {
        Message::invite($this->_to, $this->_id, $this->_invite);
    }
}
