<?php

namespace Moxl\Xec\Action\Pubsub;

use Moxl\Xec\Action;
use Moxl\Stanza\Pubsub;
use Moxl\Stanza\PubsubAtom;
use Moxl\Xec\Action\Pubsub\PostDelete;
use Moxl\Xec\Action\Pubsub\Errors;

class TestPostPublish extends Errors
{
    private $_node;
    private $_to;
    private $_id = 'test_post';

    public function request() 
    {
        $this->store();
        Pubsub::testPostPublish($this->_to, $this->_node, $this->_id);
    }
    
    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }
    
    public function setNode($node)
    {
        $this->_node = $node;
        return $this;
    }

    public function handle($stanza, $parent = false) {
        Pubsub::postDelete($this->_to, $this->_node, $this->_id);

        $this->pack(array('to' => $this->_to, 'node' => $this->_node));
        $this->deliver();
    }

    public function error($stanza, $parent = false) {
        $this->pack(array('to' => $this->_to, 'node' => $this->_node));
        $this->deliver();
    }
}
