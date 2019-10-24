<?php

namespace Moxl\Xec\Action\Pubsub;

use Moxl\Stanza\Pubsub;
use Moxl\Xec\Action\Pubsub\Errors;

class TestPostPublish extends Errors
{
    protected $_node;
    protected $_to;
    protected $_id = 'test_post';

    public function request()
    {
        $this->store();
        Pubsub::testPostPublish($this->_to, $this->_node, $this->_id);
    }

    public function handle($stanza, $parent = false)
    {
        Pubsub::postDelete($this->_to, $this->_node, $this->_id);

        $this->pack(['to' => $this->_to, 'node' => $this->_node]);
        $this->deliver();
    }

    public function error($stanza, $parent = false)
    {
        $this->pack(['to' => $this->_to, 'node' => $this->_node]);
        $this->deliver();
    }
}
