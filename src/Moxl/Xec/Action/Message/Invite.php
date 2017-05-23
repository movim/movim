<?php

namespace Moxl\Xec\Action\Message;

use Moxl\Xec\Action;
use Moxl\Stanza\Message;

class Invite extends Action
{
    private $_to;
    private $_content;
    private $_id;
    private $_invite;

    public function request()
    {
        Message::invite($this->_to, $this->_id, $this->_invite);
    }

    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }

    public function setContent($content)
    {
        $this->_content = $content;
        return $this;
    }

    public function setId($id)
    {
        $this->_id = $id;
        return $this;
    }

    public function setInvite($invite)
    {
        $this->_invite = $invite;
        return $this;
    }

    public function handle($stanza, $parent = false)
    {
    }
}
