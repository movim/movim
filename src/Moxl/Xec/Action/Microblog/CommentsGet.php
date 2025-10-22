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
        Pubsub::getItems($this->_to, $this->_node, paging: 100);
    }

    public function setId($id)
    {
        $this->_node = Post::COMMENTS_NODE . '/' . $id;
        return $this;
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        Post::where('parent_id', $this->_parentid)->delete();

        if ($stanza->pubsub->items->item) {
            foreach ($stanza->pubsub->items->item as $item) {
                $p = Post::firstOrNew([
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
