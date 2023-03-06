<?php

namespace Moxl\Xec\Action\IqGateway;

use Moxl\Xec\Action;
use Moxl\Stanza\IqGateway;

class Get extends Action
{
    protected $_to;

    public function request()
    {
        $this->store();
        IqGateway::get($this->_to);
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $this->prepare($stanza, $parent);
        $this->pack($stanza->query);
        $this->deliver();
    }
}
