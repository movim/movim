<?php

namespace Moxl\Xec\Action\Ack;

use Moxl\Xec\Action;
use Moxl\Stanza\Ack;

class Send extends Action
{
    protected $_to;
    protected $_id;

    public function request()
    {
        $this->store();
        Ack::send($this->_to, $this->_id);
    }
}
