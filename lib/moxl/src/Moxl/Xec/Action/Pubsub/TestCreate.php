<?php

namespace Moxl\Xec\Action\Pubsub;

use Moxl\Xec\Action\Pubsub\Errors;
use Moxl\Stanza\Pubsub;

class TestCreate extends Errors
{
    protected $_to;
    protected $_node = 'test_node';

    public function request()
    {
        $this->store();
        Pubsub::create($this->_to, $this->_node, 'Test');
    }

    public function handle($stanza, $parent = false)
    {
        if ($stanza['type'] == 'result') {
            // We delete the test node we just created
            Pubsub::delete($this->_to, $this->_node);

            // And we say that all it's ok
            $this->pack($this->_to);
            $this->deliver();
        }
    }

    public function error($error)
    {
        $this->pack($this->_to);
        $this->deliver();
    }
}
