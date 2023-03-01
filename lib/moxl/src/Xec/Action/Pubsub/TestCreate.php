<?php

namespace Moxl\Xec\Action\Pubsub;

use Moxl\Xec\Action;
use Moxl\Stanza\Pubsub;

class TestCreate extends Action
{
    protected $_to;
    protected $_node = 'test_node';

    public function request()
    {
        $this->store();
        Pubsub::create($this->_to, $this->_node, 'Test');
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        if ($stanza['type'] == 'result') {
            // We delete the test node we just created
            Pubsub::delete($this->_to, $this->_node);

            // And we say that all it's ok
            $this->pack($this->_to);
            $this->deliver();
        }
    }

    public function error(string $errorId, ?string $message = null)
    {
        $this->pack($this->_to);
        $this->deliver();
    }
}
