<?php

namespace Moxl\Xec\Action\Vcard4;

use Moxl\Xec\Action;
use Moxl\Stanza\Vcard4;

class Set extends Action
{
    private $_data;

    public function request()
    {
        $this->store();
        Vcard4::set($this->_data);
    }

    public function setData($data)
    {
        $this->_data = $data;
        return $this;
    }

    public function handle($stanza, $parent = false)
    {
        $this->pack(\App\User::me()->contact);
        $this->deliver();
    }
}
