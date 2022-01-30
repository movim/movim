<?php

namespace Moxl\Xec\Action\Message;

use Moxl\Xec\Action;
use Moxl\Stanza\Message;
use Moxl\Stanza\Muc;

class Reactions extends Action
{
    protected $_to;
    protected $_muc;
    protected $_id;
    protected $_parentId;
    protected $_reactions;

    public function request()
    {
        $this->store();

        if ($this->_muc) {
            Muc::message($this->_to, false, false, $this->_id, false, false, $this->_parentid, $this->_reactions);
        } else {
            Message::simpleMessage($this->_to, false, false, $this->_id, false, false, $this->_parentid, $this->_reactions);
        }
    }

    public function setReactions(array $reactions)
    {
        $this->_reactions = $reactions;
        return $this;
    }

    public function setMuc()
    {
        $this->_muc = true;
        return $this;
    }

    public function handle($stanza, $parent = false)
    {
        if ($this->_muc) {
            $m = new \Moxl\Xec\Payload\Message;
            $m->handle($stanza, $parent);
        }
    }
}
