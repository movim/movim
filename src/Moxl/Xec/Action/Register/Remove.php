<?php

namespace Moxl\Xec\Action\Register;

use Moxl\Xec\Action;
use Moxl\Stanza\Register;

class Remove extends Action
{
    protected ?string $_to = null;

    public function request()
    {
        $this->store();
        Register::remove($this->_to);
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        if ($this->_to == null) {
            $this->deliver();
        }
    }

    public function error(string $errorId, ?string $message = null)
    {
        // We don't handle errors for now if we unregister from a specific thing
        if ($this->_to == null) {
            $this->pack($message);
            $this->deliver();
        }
    }
}
