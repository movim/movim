<?php

namespace Moxl\Xec\Action\Pubsub;

use Moxl\Stanza\Pubsub;
use Moxl\Xec\Action;

class PostDelete extends Action
{
    protected $_to;
    protected $_id;
    protected $_node;

    public function request()
    {
        $this->store();
        Pubsub::itemDelete($this->_to, $this->_node, $this->_id);
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        \App\Post::where('server', $this->_to)->where('node', $this->_node)
            ->where('nodeid', $this->_id)->delete();

        $this->pack([
            'server' => $this->_to,
            'node' => $this->_node,
            'id' => $this->_id
        ]);

        $this->deliver();
    }

    public function error(string $errorId, ?string $message = null)
    {
        \App\Post::where('server', $this->_to)->where('node', $this->_node)
            ->where('nodeid', $this->_id)->delete();
    }
}
