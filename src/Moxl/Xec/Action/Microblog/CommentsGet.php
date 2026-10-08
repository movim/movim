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
        $this->iq(Pubsub::getItems($this->_node, paging: 500), to: $this->_to, type: 'get');
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
            $comments = collect();

            foreach ($stanza->pubsub->items->item as $item) {
                $comment = new Post([
                    'server' => $this->_to,
                    'node' => $this->_node,
                    'nodeid' => (string)$item->attributes()->id
                ]);
                $comment->set($item);
                $comment->parent_id = $this->_parentid;

                $comments[$comment->nodeid] = $comment->toArray();
            }

            if ($comments->isNotEmpty()) {
                Post::where('server', $this->_to)->where('node', $this->_node)->delete();
                Post::insert($comments->toArray());
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
