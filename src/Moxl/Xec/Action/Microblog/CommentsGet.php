<?php

namespace Moxl\Xec\Action\Microblog;

use Moxl\Xec\Action;
use Moxl\Stanza\Pubsub;

class CommentsGet extends Action
{
    private $_to;
    private $_id;
    private $_node;
    private $_parentid;

    public function request()
    {
        $this->store();
        Pubsub::getItems($this->_to, $this->_node);
    }

    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }

    public function setId($id)
    {
        $this->_id = $id;
        $this->_node = 'urn:xmpp:microblog:0:comments/'.$this->_id;
        return $this;
    }

    public function setParentId($parentid)
    {
        $this->_parentid = $parentid;
        return $this;
    }

    public function handle($stanza, $parent = false)
    {
        $node = (string)$stanza->pubsub->items->attributes()->node;

        if($stanza->pubsub->items->item) {
            foreach($stanza->pubsub->items->item as $item) {
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

    public function error()
    {
        $this->pack($this->_id);
        $this->deliver();
    }

}
