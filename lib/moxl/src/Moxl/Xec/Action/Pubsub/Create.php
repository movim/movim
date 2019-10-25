<?php

namespace Moxl\Xec\Action\Pubsub;

use Moxl\Xec\Action\Pubsub\Errors;
use Moxl\Stanza\Pubsub;

class Create extends Errors
{
    protected $_to;
    protected $_node;
    protected $_name;

    public function request()
    {
        $this->store();
        Pubsub::create($this->_to, $this->_node, $this->_name);
    }

    public function handle($stanza, $parent = false)
    {
        if ($stanza["type"] == "result") {
            $this->pack(['server' => $this->_to, 'node' => $this->_node]);
            $this->deliver();
        }
    }

    public function error($error)
    {
        $this->event('creationerror', $this->_node);
    }
}
