<?php

namespace Moxl\Xec\Action\Message;

use Moxl\Xec\Action;
use Moxl\Stanza\Message;

class MDSDisplayed extends Action
{
    protected $_to;
    protected $_id;

    public function request()
    {
        $this->store();
        $this->iq(Message::mdsDisplay($this->_to, $this->me->id, $this->_id), type: 'set');
    }
}
