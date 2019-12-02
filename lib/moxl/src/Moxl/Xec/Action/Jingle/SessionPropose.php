<?php

namespace Moxl\Xec\Action\Jingle;

use Moxl\Xec\Action;
use Moxl\Stanza\Jingle;

class SessionPropose extends Action
{
    protected $_to;
    protected $_id;

    public function request()
    {
        $this->store();
        Jingle::sessionPropose($this->_to, $this->_id);
    }
}
