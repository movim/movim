<?php

namespace Moxl\Xec\Action\Jingle;

use Moxl\Xec\Action;
use Moxl\Stanza\Jingle;

class SessionUnmute extends Action
{
    protected $_to;
    protected $_id;
    protected $_name = false;

    public function request()
    {
        $this->store();
        Jingle::sessionMute($this->_to, $this->_id, $this->_name);
    }
}
