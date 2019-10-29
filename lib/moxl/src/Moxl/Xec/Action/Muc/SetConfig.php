<?php

namespace Moxl\Xec\Action\Muc;

use Moxl\Xec\Action;
use Moxl\Stanza\Muc;

class SetConfig extends Action
{
    protected $_to;
    protected $_data;

    public function request()
    {
        $this->store();
        Muc::setConfig($this->_to, $this->_data);
    }

    public function handle($stanza, $parent = false)
    {
        $this->pack($this->_to);
        $this->deliver();
    }

    public function error($id, $message = false)
    {
        if ($message) {
            $this->pack($message);
            $this->deliver();
        }
    }
}
