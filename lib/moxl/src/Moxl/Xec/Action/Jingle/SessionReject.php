<?php

namespace Moxl\Xec\Action\Jingle;

use Moxl\Xec\Action;
use Moxl\Stanza\Jingle;

class SessionReject extends Action
{
    protected $_to;
    protected $_id;

    public function request()
    {
        $this->store();
        Jingle::sessionReject($this->_id);
        Jingle::sessionReject($this->_id, $this->_to);
    }
}
