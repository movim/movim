<?php

namespace Moxl\Xec\Action\Message;

use Moxl\Xec\Action;
use Moxl\Stanza\Message;

class Inactive extends Action
{
    protected $_to;
    protected bool $_muc = false;

    public function request()
    {
        $this->store();
        $this->send(
            Message::maker(
                $this->_to,
                type: $this->_muc ? 'groupchat' : 'chat',
                chatstates: 'inactive'
            )
        );
    }

    public function setMuc()
    {
        $this->_muc = true;
        return $this;
    }
}
