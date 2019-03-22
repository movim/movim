<?php

namespace Moxl\Xec\Action\Message;

use Moxl\Xec\Action;
use Moxl\Stanza\Message;
use Moxl\Stanza\Muc;

class Composing extends Action
{
    protected $_to;
    protected $_muc = false;

    public function request()
    {
        $this->store();
        if ($this->_muc) {
            Muc::composing($this->_to);
        } else {
            Message::composing($this->_to);
        }
    }

    public function setMuc()
    {
        $this->_muc = true;
        return $this;
    }
}
