<?php

namespace Moxl\Xec\Action\Presence;

use Moxl\Xec\Action;
use Moxl\Stanza\Presence;

class Subscribed extends Action
{
    protected $_to;

    public function request()
    {
        $this->store();
        Presence::subscribed($this->_to);
    }

    public function handle($stanza, $parent = false)
    {
        $this->pack($this->_to);
        $this->deliver();
    }
}
