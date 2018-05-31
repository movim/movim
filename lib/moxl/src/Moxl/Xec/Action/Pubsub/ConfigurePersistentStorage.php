<?php

namespace Moxl\Xec\Action\Pubsub;

use Moxl\Xec\Action;
use Moxl\Stanza\Pubsub;

class ConfigurePersistentStorage extends Action
{
    protected $_to;
    protected $_node;
    protected $_access_model;
    protected $_max_items;

    public function request()
    {
        $this->store();
        Pubsub::configurePersistentStorage($this->_to, $this->_node, $this->_access_model, $this->_max_items);
    }

    public function setAccessPresence()
    {
        $this->_access_model = 'presence';
        return $this;
    }

    public function setMaxItems($max)
    {
        $this->_max_items = $max;
        return $this;
    }

    public function handle($stanza, $parent = false)
    {
        $this->pack($this->_node);
        $this->deliver();
    }

    public function errorFeatureNotImplemented($error)
    {
        $this->pack($this->_node);
        $this->deliver();
    }

    public function errorItemNotFound($error)
    {
        $this->pack($this->_node);
        $this->deliver();
    }
}
