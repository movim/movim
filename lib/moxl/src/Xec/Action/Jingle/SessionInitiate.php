<?php

namespace Moxl\Xec\Action\Jingle;

use Moxl\Xec\Action;
use Moxl\Stanza\Jingle;

class SessionInitiate extends Action
{
    protected $_to;
    protected $_offer;

    public function request()
    {
        $this->store();
        Jingle::sessionInitiate($this->_to, $this->_offer);
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
