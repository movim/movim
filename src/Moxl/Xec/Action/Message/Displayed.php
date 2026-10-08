<?php

namespace Moxl\Xec\Action\Message;

use Moxl\Xec\Action;
use Moxl\Stanza\Message;

class Displayed extends Action
{
    protected $_to;
    protected $_id;
    protected $_type;

    public function request()
    {
        $this->send(Message::maker(
            to: $this->_to,
            messageId: generateUUID(),
            id: $this->_id,
            type: $this->_type,
            receipts: 'displayed'
        ));
    }
}
