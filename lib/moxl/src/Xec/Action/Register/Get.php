<?php

namespace Moxl\Xec\Action\Register;

use Moxl\Xec\Action;
use Moxl\Stanza\Register;

class Get extends Action
{
    protected $_to;

    public function request()
    {
        $this->store();
        Register::get($this->_to);
    }

    public function handle($stanza, $parent = false)
    {
        $this->prepare($stanza, $parent);
        $this->pack($stanza->query, $this->_to);
        $this->deliver();
    }

    public function error($stanza)
    {
        $this->deliver();
    }

    public function errorFeatureNotImplemented()
    {
        $this->deliver();
    }

    public function errorServiceUnavailable()
    {
        $this->deliver();
    }
}
