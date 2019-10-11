<?php

namespace Moxl\Xec\Action\Message;

use Moxl\Xec\Action;
use Moxl\Stanza\Message;
use Moxl\Stanza\Muc;

class Active extends Action
{
    protected $_to;
    protected $_muc = false;

    public function request()
    {
        $this->store();
        if ($this->_muc) {
            Muc::active($this->_to);
        } else {
            Message::active($this->_to);
        }
    }

    public function setMuc()
    {
        $this->_muc = true;
        return $this;
    }
}
