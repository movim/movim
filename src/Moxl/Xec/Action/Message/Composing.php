<?php

namespace Moxl\Xec\Action\Message;

use Moxl\Xec\Action;
use Moxl\Stanza\Message;
use Moxl\Stanza\Muc;

class Composing extends Action
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
                chatstates: 'composing'
            )
        );
    }

    public function setMuc()
    {
        $this->_muc = true;
        return $this;
    }
}
