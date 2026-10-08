<?php

namespace Moxl\Xec\Action\Jingle;

use Moxl\Stanza\Jingle;
use Moxl\Xec\Action;

class SessionUnmute extends Action
{
    protected $_to;

    protected $_id;

    protected $_name = false;

    public function request()
    {
        $this->store();
        $this->iq(Jingle::sessionUnmute($this->_id, $this->_name), to: $this->_to, type: 'set');
    }
}
