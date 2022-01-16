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
        $this->store($this->_id);
        Message::invite($this->_to, $this->_id, $this->_invite);
    }

    public function handle($stanza, $parent = false)
    {
        $this->deliver();
    }

    public function error($id, $message = false)
    {
        if ($message) {
            $this->pack($message);
            $this->deliver();
        }
    }
}
