<?php

namespace Moxl\Xec\Action\Pubsub;

use Moxl\Xec\Action;
use Moxl\Stanza\Pubsub;
use Moxl\Xec\Action\Pubsub\Errors;

class PostDelete extends Errors
{
    private $_to;
    private $_id;
    private $_node;

    public function request()
    {
        $this->store();
        Pubsub::postDelete($this->_to, $this->_node, $this->_id);
    }

    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }

    public function setId($id)
    {
        $this->_id = $id;
        return $this;
    }

    public function setNode($node)
    {
        $this->_node = $node;
        return $this;
    }

    public function handle($stanza, $parent = false)
    {
        \App\Post::where('server', $this->_to)->where('node', $this->_node)
                 ->where('nodeid', $this->_id)->delete();

        $this->pack([
            'server' => $this->_to,
            'node' => $this->_node,
            'id' => $this->_id]);

        $this->deliver();
    }

    public function error($stanza)
    {
        \App\Post::where('server', $this->_to)->where('node', $this->_node)
                 ->where('nodeid', $this->_id)->delete();
    }
}
