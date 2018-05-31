<?php

namespace Moxl\Xec\Action\Presence;

use Moxl\Xec\Action;
use Moxl\Stanza\Presence;

class Subscribe extends Action
{
    protected $_to;
    protected $_status;

    public function request()
    {
        $this->store();
        Presence::subscribe($this->_to, $this->_status);
    }

    public function handle($stanza, $parent = false)
    {
        $this->pack($this->_to);
        $this->deliver();
    }
}
