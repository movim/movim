<?php

namespace Moxl\Xec\Action\Pubsub;

use Moxl\Xec\Action;
use Moxl\Stanza\Pubsub;
use Moxl\Xec\Action\Pubsub\Errors;

class SetConfig extends Errors
{
    protected $_to;
    protected $_node;
    protected $_data;

    public function request()
    {
        $this->store();
        Pubsub::setConfig($this->_to, $this->_node, $this->_data);
    }

    public function handle($stanza, $parent = false)
    {
        $this->deliver();
    }
}
