<?php

namespace Moxl\Xec\Action\Microblog;

use Moxl\Xec\Action;
use Moxl\Stanza\Pubsub;

class CommentCreateNode extends Action
{
    protected $_to;
    protected $_parentid;

    public function request()
    {
        $this->store();
        Pubsub::createCommentNode($this->_to, $this->_parentid);
    }

    public function handle($stanza, $parent = false)
    {
        $this->pack(['server' => $this->_to, 'parentid' => $this->_parentid]);
        $this->deliver();
    }
}
