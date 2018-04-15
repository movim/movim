<?php

namespace Moxl\Xec\Action\Pubsub;

use Moxl\Xec\Action;
use Moxl\Xec\Action\Pubsub\Errors;
use Moxl\Stanza\Pubsub;

class Create extends Errors
{
    private $_to;
    private $_node;
    private $_name;

    public function request()
    {
        $this->store();
        Pubsub::create($this->_to, $this->_node, $this->_name);
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

    public function setName($name)
    {
        $this->_name = $name;
        return $this;
    }

    public function handle($stanza, $parent = false)
    {
        if($stanza["type"] == "result"){
            $this->pack(['server' => $this->_to, 'node' => $this->_node]);
            $this->deliver();
        }
    }

    public function error($error)
    {
        $this->event('creationerror', $this->_node);
    }
}
