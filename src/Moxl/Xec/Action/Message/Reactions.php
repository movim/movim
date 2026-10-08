<?php

namespace Moxl\Xec\Action\Message;

use Moxl\Xec\Action;
use Moxl\Stanza\Message;

class Reactions extends Action
{
    protected $_to;
    protected $_muc;
    protected $_id;
    protected $_parentid;
    protected $_reactions;

    public function request()
    {
        $messageId = $this->store();

        $this->send(Message::maker(
            to: $this->_to,
            messageId: $messageId,
            id: $this->_id,
            type: $this->_muc ? 'groupchat' : 'chat',
            parentId: $this->_parentid,
            reactions: $this->_reactions
        ));
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

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        if ($this->_muc) {
            $m = new \Moxl\Xec\Payload\Message($this->me, sessionId: $this->sessionId);
            $m->handle($stanza, $parent);
        }
    }
}
