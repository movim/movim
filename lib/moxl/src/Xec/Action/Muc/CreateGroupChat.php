<?php

namespace Moxl\Xec\Action\Muc;

use Moxl\Xec\Action;
use Moxl\Stanza\Muc;

class CreateGroupChat extends Action
{
    protected $_to;
    protected $_name;

    public function request()
    {
        $this->store();
        Muc::createGroupChat($this->_to, $this->_name);
    }

    public function handle($stanza, $parent = false)
    {
        $this->pack([
            'jid' => $this->_to,
            'name' => $this->_name,
            'nick' => $this->_nick,
            'autojoin' => $this->_autojoin,
            'pinned' => $this->_pinned,
            'notify' => $this->_notify,
        ]);
        $this->deliver();
    }
}
