<?php

namespace Moxl\Xec\Action\AdHoc;

use Moxl\Xec\Action;
use Moxl\Stanza\AdHoc;

class Get extends Action
{
    protected $_to;

    public function request()
    {
        $this->store();
        AdHoc::get($this->_to);
    }

    public function handle($stanza, $parent = false)
    {
        $this->pack($stanza->query->item);
        $this->deliver();
    }
}
