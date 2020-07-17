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

    public function errorItemNotFound($stanza)
    {
        $this->deliver();
    }

    public function errorUnexpectedRequest($stanza)
    {
        $this->deliver();
    }
}
