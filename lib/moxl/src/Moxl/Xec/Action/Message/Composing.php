<?php

namespace Moxl\Xec\Action\Message;

use Moxl\Xec\Action;
use Moxl\Stanza\Message;

class Composing extends Action
{
    protected $_to;

    public function request()
    {
        Message::composing($this->_to);
    }

    public function handle($stanza, $parent = false)
    {
    }
}
