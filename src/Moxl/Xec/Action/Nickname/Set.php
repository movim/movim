<?php

namespace Moxl\Xec\Action\Nickname;

use Moxl\Xec\Action;
use Moxl\Stanza\Nickname;

class Set extends Action
{
    protected $_nickname;

    public function request()
    {
        $this->store();
        $this->iq(Nickname::set($this->_nickname), type: 'set');
    }
}
