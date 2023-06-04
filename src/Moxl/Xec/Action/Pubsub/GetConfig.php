<?php

namespace Moxl\Xec\Action\Pubsub;

use Moxl\Stanza\Pubsub;
use Moxl\Xec\Action;

class GetConfig extends Action
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

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
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
