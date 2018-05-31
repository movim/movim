<?php

namespace Moxl\Xec\Action\Message;

use Moxl\Xec\Action;
use Moxl\Stanza\Message;

class Paused extends Action
{
    protected $_to;

    public function request()
    {
        Message::paused($this->_to);
    }

    public function handle($stanza, $parent = false)
    {
    }
}
