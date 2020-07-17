<?php

namespace Moxl\Xec\Action\Muc;

use Moxl\Xec\Action;
use Moxl\Stanza\Muc;

class SetRole extends Action
{
    protected $_to;
    protected $_nick;
    protected $_role;

    public function request()
    {
        $this->store();
        Muc::setRole($this->_to, $this->_nick, $this->_role);
    }

    public function handle($stanza, $parent = false)
    {
        $this->deliver();
    }
}
