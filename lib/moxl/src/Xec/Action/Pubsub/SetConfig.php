<?php

namespace Moxl\Xec\Action\Pubsub;

use Moxl\Stanza\Pubsub;
use Moxl\Xec\Action;

class SetConfig extends Action
{
    protected $_to;
    protected $_node;
    protected $_data;

    public function request()
    {
        $this->store();
        Pubsub::setConfig($this->_to, $this->_node, $this->_data);
    }

    public function error(string $errorId, ?string $message = null)
    {
        $this->pack($message);
        $this->deliver();
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $this->deliver();
    }
}
