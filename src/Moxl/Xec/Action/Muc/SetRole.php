<?php

namespace Moxl\Xec\Action\Muc;

use Moxl\Stanza\Muc;
use Moxl\Xec\Action;

class SetRole extends Action
{
    protected $_to;

    protected $_nick;

    protected $_role;

    public function request()
    {
        $this->store();
        $this->iq(Muc::setRole($this->_nick, $this->_role), to: $this->_to, type: 'set');
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $this->deliver();
    }
}
