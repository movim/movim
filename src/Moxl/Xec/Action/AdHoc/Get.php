<?php

namespace Moxl\Xec\Action\AdHoc;

use Moxl\Stanza\AdHoc;
use Moxl\Xec\Action;

class Get extends Action
{
    protected $_to;

    public function request()
    {
        $this->store();
        $this->iq(AdHoc::get(), to: $this->_to, type: 'get');
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $this->pack($stanza->query->item, $this->_to);
        $this->deliver();
    }

    public function error(string $errorId, ?string $message = null)
    {
        $this->pack($message, $this->_to);
        $this->deliver();
    }
}
