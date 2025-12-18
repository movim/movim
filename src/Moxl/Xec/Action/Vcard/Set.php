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
        $this->iq(Vcard::set($this->_data), to: $this->_to, type: 'set');
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $this->pack($this->_to);
        $this->deliver();
    }
}
