<?php

namespace Moxl\Xec\Action\Jingle;

use Moxl\Xec\Action;
use Moxl\Stanza\Jingle;

class ContentModify extends Action
{
    protected $_to;
    protected $_jingle;

    public function request()
    {
        $this->store();
        $this->iq($this->_jingle, to: $this->_to, type: 'set');
    }
}
