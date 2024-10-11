<?php

namespace Moxl\Xec\Action\JingleCallInvite;

use Moxl\Stanza\JingleCallInvite;
use Moxl\Xec\Action;

class Left extends Action
{
    protected string $_to;
    protected string $_id;

    public function request()
    {
        $this->store();
        JingleCallInvite::left($this->_to, $this->_id);
    }
}
