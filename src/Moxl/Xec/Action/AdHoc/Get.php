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

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $this->pack($stanza->query->item, $this->_to);
        $this->deliver();
    }
}
