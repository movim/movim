<?php

namespace Moxl\Xec\Action\Pubsub;

use Moxl\Xec\Action;
use Moxl\Stanza\Pubsub;

class CreatePersistentStorage extends Action
{
    protected $_to;
    protected $_node;

    public function request()
    {
        $this->store();
        Pubsub::createPersistentStorage($this->_to, $this->_node);
    }

    public function handle($stanza, $parent = false)
    {
        $this->pack($this->_node);
        $this->deliver();
    }

    public function errorConflict($error)
    {
        $this->pack($this->_node);
        $this->deliver();
    }
}
