<?php

namespace Moxl\Xec\Action\BOB;

use Moxl\Xec\Action;
use Moxl\Stanza\BOB;

class Answer extends Action
{
    protected $_to;
    protected $_base64;
    protected $_cid;
    protected $_type;
    protected $_id;

    public function request()
    {
        $this->store();
        BOB::answer($this->_to, $this->_id, $this->_cid, $this->_type, $this->_base64);
    }
}
