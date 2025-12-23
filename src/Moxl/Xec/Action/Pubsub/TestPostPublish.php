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
        $this->iq(Pubsub::testPostPublish($this->_node, $this->_id), to: $this->_to, type: 'set');
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $this->iq(Pubsub::itemDelete($this->_node, $this->_id), to: $this->_to, type: 'set');

        $this->pack(['to' => $this->_to, 'node' => $this->_node]);
        $this->deliver();
    }

    public function error(string $errorId, ?string $message = null)
    {
        $this->pack(['to' => $this->_to, 'node' => $this->_node]);
        $this->deliver();
    }
}
