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
        Roster::remove($this->_to);
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $contact = \App\User::me()
              ->session
              ->contacts()
              ->where('jid', $this->_to)
              ->first();
        if ($contact) {
            $contact->delete();
        }

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
