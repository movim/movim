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
        $this->iq(Pubsub::createCommentNode($this->_parentid), to: $this->_to, type: 'set');
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $this->pack(['server' => $this->_to, 'parentid' => $this->_parentid]);
        $this->deliver();
    }
}
