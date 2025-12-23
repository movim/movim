<?php

namespace Moxl\Xec\Action\Blocking;

use Moxl\Stanza\Blocking;
use Moxl\Xec\Action;

class Unblock extends Action
{
    protected $_jid;

    public function request()
    {
        $this->store();
        $this->iq(Blocking::unblock($this->_jid), type: 'set');
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $this->me->reported()->detach($this->_jid);
        $this->me->refreshBlocked();

        $this->pack($this->_jid);
        $this->deliver();
    }
}
