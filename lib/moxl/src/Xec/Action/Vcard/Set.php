<?php

namespace Moxl\Xec\Action\Vcard;

use Moxl\Xec\Action;
use Moxl\Stanza\Vcard;

class Set extends Action
{
    protected $_to = false;
    protected $_data;

    public function request()
    {
        $this->store();
        Vcard::set($this->_to, $this->_data);
    }

    public function handle($stanza, $parent = false)
    {
        $this->pack($this->_to);
        $this->deliver();
    }
}
