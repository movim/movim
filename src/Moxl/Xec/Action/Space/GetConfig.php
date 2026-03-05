<?php

namespace Moxl\Xec\Action\Space;

use Moxl\Stanza\Pubsub;
use Moxl\Xec\Action;

class GetConfig extends Action
{
    protected $_to;
    protected $_node;

    public function request()
    {
        $this->store();
        $this->iq(Pubsub::getConfig($this->_node), to: $this->_to, type: 'get');
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $this->pack([
            'server' => $this->_to,
            'node' => $this->_node,
            'config' => $stanza->pubsub->configure,
        ]);
        $this->deliver();
    }
}
