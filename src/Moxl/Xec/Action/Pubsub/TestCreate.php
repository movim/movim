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
        $this->iq(Pubsub::create($this->_node, 'Test'), to: $this->_to, type: 'set');
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        if ($stanza['type'] == 'result') {
            // We delete the test node we just created
            $this->iq(Pubsub::delete($this->_node), to: $this->_to, type: 'set');

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
