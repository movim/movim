<?php

namespace Moxl\Xec\Action\JingleCallInvite;

use Moxl\Stanza\JingleCallInvite;
use Moxl\Xec\Action;

class Accept extends Action
{
    protected string $_to;
    protected string $_id;

    public function request()
    {
        $this->store();
        JingleCallInvite::accept($this->_to, $this->_id);
    }
}
