<?php

namespace Moxl\Xec\Action\Jingle;

use Moxl\Xec\Action;
use Moxl\Stanza\Jingle;

class SessionInitiate extends Action
{
    protected $_to;
    protected $_jingle;

    public function request()
    {
        $this->store();
        $this->iq($this->_jingle, to: $this->_to, type: 'set');
    }

    public function errorItemNotFound(string $errorId, ?string $message = null)
    {
        $this->deliver();
    }

    public function errorUnexpectedRequest(string $errorId, ?string $message = null)
    {
        $this->deliver();
    }
}
