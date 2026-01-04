<?php

namespace Moxl\Xec\Action\Message;

use Moxl\Xec\Action;
use Moxl\Stanza\Message;

class Moderate extends Action
{
    protected $_to;
    protected $_stanzaid;

    public function request()
    {
        $this->iq(Message::moderate($this->_stanzaid), to: $this->_to, type: 'set');
    }
}
