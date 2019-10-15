<?php

namespace Moxl\Xec\Action\Muc;

use Moxl\Xec\Action;
use Moxl\Stanza\Muc;

class Destroy extends Action
{
    protected $_to;

    public function request()
    {
        $this->store();
        Muc::destroy($this->_to);
    }

    public function handle($stanza, $parent = false)
    {
        $this->pack($this->_to);
        $this->deliver();
    }
}
