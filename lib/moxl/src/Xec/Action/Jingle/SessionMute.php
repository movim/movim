<?php

namespace Moxl\Xec\Action\Jingle;

use Moxl\Xec\Action;
use Moxl\Stanza\Jingle;

class SessionMute extends Action
{
    protected $_to;
    protected $_id;
    protected $_name = false;

    public function request()
    {
        $this->store();
        Jingle::sessionUnmute($this->_to, $this->_id, $this->_name);
    }
}
