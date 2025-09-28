<?php

namespace Moxl\Xec\Action\Microblog;

use App\Post;
use Moxl\Xec\Action;
use Moxl\Stanza\Pubsub;

class CommentsGet extends Action
{
    protected $_to;
    protected $_node;
    protected $_parentid;

    public function request()
    {
        $this->store();
        Pubsub::getItems($this->_to, $this->_node);
    }

    public function setId($id)
    {
        $this->_node = Post::COMMENTS_NODE . '/' . $id;
        return $this;
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        if ($stanza->pubsub->items->item) {
            foreach ($stanza->pubsub->items->item as $item) {
                $p = \App\Post::firstOrNew([
                    'server' => $this->_to,
                    'node' => $this->_node,
                    'nodeid' => (string)$item->attributes()->id
                ]);
                $p->set($item);
                $p->parent_id = $this->_parentid;
                $p->save();
            }
        }

        $this->pack($this->_parentid);
        $this->deliver();
    }

    public function error(string $errorId, ?string $message = null)
    {
        $this->pack($this->_parentid);
        $this->deliver();
    }
}
