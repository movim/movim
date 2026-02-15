<?php

namespace Moxl\Xec\Action\Space;

use Moxl\Stanza\Pubsub;
use Moxl\Xec\Action;

class Unsubscribe extends Action
{
    protected $_to;
    protected $_from;
    protected $_node;
    protected $_subid;

    public function request()
    {
        $this->store();
        $this->iq(Pubsub::unsubscribe($this->_from, $this->_node, $this->_subid), to: $this->_to, type: 'set');
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $this->pack(['server' => $this->_to, 'node' => $this->_node]);
        $this->deliver();
    }

    public function error(string $errorId, ?string $message = null)
    {
        $this->pack(['server' => $this->_to, 'node' => $this->_node]);
        $this->deliver();
    }
}
