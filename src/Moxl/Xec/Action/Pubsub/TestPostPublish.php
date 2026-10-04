<?php

namespace Moxl\Xec\Action\Pubsub;

use Moxl\Stanza\Pubsub;
use Moxl\Xec\Action;

class TestPostPublish extends Action
{
    protected string $_node;
    protected string $_to;
    public const TEST_POST_ID = 'test_post';

    public function request()
    {
        $this->store();
        $this->iq(Pubsub::testPostPublish($this->_node, TestPostPublish::TEST_POST_ID), to: $this->_to, type: 'set');
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $delete = new PostDelete(me: $this->me, sessionId: $this->sessionId);
        $delete->setTo($this->_to)
               ->setNode($this->_node)
               ->setId(TestPostPublish::TEST_POST_ID)
               ->request();

        $this->pack(['to' => $this->_to, 'node' => $this->_node]);
        $this->deliver();
    }

    public function error(string $errorId, ?string $message = null)
    {
        $this->pack(['to' => $this->_to, 'node' => $this->_node]);
        $this->deliver();
    }
}
