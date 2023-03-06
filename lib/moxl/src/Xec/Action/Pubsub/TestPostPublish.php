<?php

namespace Moxl\Xec\Action\Pubsub;

use Moxl\Stanza\Pubsub;
use Moxl\Xec\Action;

class TestPostPublish extends Action
{
    protected $_node;
    protected $_to;
    protected $_id = 'test_post';

    public function request()
    {
        $this->store();
        Pubsub::testPostPublish($this->_to, $this->_node, $this->_id);
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        Pubsub::postDelete($this->_to, $this->_node, $this->_id);

        $this->pack(['to' => $this->_to, 'node' => $this->_node]);
        $this->deliver();
    }

    public function error(string $errorId, ?string $message = null)
    {
        $this->pack(['to' => $this->_to, 'node' => $this->_node]);
        $this->deliver();
    }
}
