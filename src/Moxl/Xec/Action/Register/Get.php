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
        $this->iq(Register::get($this->_to), to: $this->_to, type: 'get');
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $this->prepare($stanza, $parent);
        $this->pack($stanza->query, $this->_to);
        $this->deliver();
    }

    public function error(string $errorId, ?string $message = null)
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
