<?php

namespace Moxl\Xec\Action\Message;

use Moxl\Xec\Action;
use Moxl\Stanza\Message;
use Moxl\Stanza\Muc;

class Paused extends Action
{
    protected $_to;
    protected $_muc = false;

    public function request()
    {
        $this->store();
        if ($this->_muc) {
            Muc::paused($this->_to);
        } else {
            Message::paused($this->_to);
        }
    }

    public function setMuc()
    {
        $this->_muc = true;
        return $this;
    }
}
