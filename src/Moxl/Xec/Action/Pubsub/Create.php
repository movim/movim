<?php

namespace Moxl\Xec\Action\Pubsub;

use Moxl\Xec\Action;
use Moxl\Stanza\Pubsub;

class Create extends Action
{
    protected $_to;
    protected $_node;
    protected $_name;

    public function request()
    {
        $this->store();
        Pubsub::create($this->_to, $this->_node, $this->_name);
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        if ($stanza["type"] == "result") {
            $this->pack(['server' => $this->_to, 'node' => $this->_node]);
            $this->deliver();
        }
    }
}
