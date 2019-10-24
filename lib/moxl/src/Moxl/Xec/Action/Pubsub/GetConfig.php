<?php

namespace Moxl\Xec\Action\Pubsub;

use Moxl\Stanza\Pubsub;
use Moxl\Xec\Action\Pubsub\Errors;

class GetConfig extends Errors
{
    protected $_to;
    protected $_node;
    protected $_advanced = false;

    public function request()
    {
        $this->store();
        Pubsub::getConfig($this->_to, $this->_node);
    }

    public function enableAdvanced()
    {
        $this->_advanced = true;
        return $this;
    }

    public function handle($stanza, $parent = false)
    {
        $this->pack([
            'config' => $stanza->pubsub->configure,
            'server' => $this->_to,
            'node' => $this->_node,
            'advanced' => $this->_advanced
        ]);
        $this->deliver();
    }
}
