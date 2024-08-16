<?php

namespace Moxl\Xec\Action\Jingle;

use Moxl\Xec\Action;
use Moxl\Stanza\Jingle;

class SessionRetract extends Action
{
    protected $_to;
    protected $_id;

    public function request()
    {
        $this->store();
        Jingle::sessionRetract($this->_to, $this->_id);
    }
}
