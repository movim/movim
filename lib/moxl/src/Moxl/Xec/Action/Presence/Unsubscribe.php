<?php

namespace Moxl\Xec\Action\Presence;

use Moxl\Xec\Action;
use Moxl\Stanza\Presence;

class Unsubscribe extends Action
{
    protected $_to;
    protected $_status;

    public function request()
    {
        $this->store();
        Presence::unsubscribe($this->_to, $this->_status);
    }
}
