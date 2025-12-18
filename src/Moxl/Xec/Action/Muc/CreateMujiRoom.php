<?php

namespace Moxl\Xec\Action\Muc;

use Moxl\Xec\Action;
use Moxl\Stanza\Muc;

class CreateMujiRoom extends Action
{
    protected $_to;

    public function request()
    {
        $this->store();
        $this->iq(Muc::createMujiChat(), to: $this->_to, type: 'set');
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $this->deliver();
    }
}
