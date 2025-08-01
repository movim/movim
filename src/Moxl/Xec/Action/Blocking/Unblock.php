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
        Blocking::unblock($this->_jid);
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        me()->reported()->detach($this->_jid);
        me()->refreshBlocked();

        $this->pack($this->_jid);
        $this->deliver();
    }
}
