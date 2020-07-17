<?php

namespace Moxl\Xec\Action\Jingle;

use Moxl\Xec\Action;
use Moxl\Stanza\Jingle;
use Movim\Session;

class SessionAccept extends Action
{
    protected $_to;
    protected $_id;

    public function request()
    {
        Session::start()->set('jingleSid', $this->_id);

        $this->store();
        Jingle::sessionAccept($this->_id);
        Jingle::sessionProceed($this->_to, $this->_id);
    }
}
