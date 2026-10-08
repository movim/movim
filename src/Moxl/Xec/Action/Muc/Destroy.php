<?php

namespace Moxl\Xec\Action\Muc;

use Moxl\Stanza\Muc;
use Moxl\Xec\Action;

class Destroy extends Action
{
    protected $_to;

    public function request()
    {
        $this->store();
        $this->iq(Muc::destroy($this->_to), to: $this->_to, type: 'set');
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $this->pack($this->_to);
        $this->deliver();
    }
}
