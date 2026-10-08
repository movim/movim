<?php

namespace Moxl\Xec\Action\Jingle;

use Moxl\Xec\Action;

class ContentAdd extends Action
{
    protected $_to;
    protected $_jingle;

    public function request()
    {
        $this->store();
        $this->iq($this->_jingle, to: $this->_to, type: 'set');
    }
}
