<?php

namespace Moxl\Xec\Action\Roster;

use Moxl\Xec\Action;
use Moxl\Stanza\Roster;

class RemoveItem extends Action
{
    protected $_to;

    public function request()
    {
        $this->store();
        $this->iq(Roster::remove($this->_to), type: 'set');
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $this->me->session
              ->contacts()
              ->where('jid', $this->_to)
              ->delete();

        $this->pack($this->_to);
        $this->deliver();
    }

    public function errorItemNotFound(string $errorId, ?string $message = null)
    {
        $this->handle();
    }

    public function errorServiceUnavailable()
    {
        $this->deliver();
    }
}
