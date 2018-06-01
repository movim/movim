<?php

namespace Moxl\Xec\Action\Register;

use Moxl\Xec\Action;
use Moxl\Stanza\Register;

class Get extends Action
{
    private $_to;

    public function request()
    {
        $this->store();
        Register::get($this->_to);
    }

    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }

    public function handle($stanza, $parent = false)
    {
        $this->prepare($stanza, $parent);
        $this->pack($stanza->query, $this->_to);
        $this->deliver();
    }

    public function errorServiceUnavailable()
    {
        $this->deliver();
    }
}
