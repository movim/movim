<?php

namespace Moxl\Xec\Action\IqGateway;

use Moxl\Xec\Action;
use Moxl\Stanza\IqGateway;

class Set extends Action
{
    protected $_to;
    protected $_prompt;
    protected $_extra;

    public function request()
    {
        $this->store();
        IqGateway::set($this->_to, $this->_prompt);
    }

    public function handle($stanza, $parent = false)
    {
        $this->prepare($stanza, $parent);
        $this->pack([
            'query' => $stanza->query,
            'extra' => $this->_extra
        ]);
        $this->deliver();
    }

    public function error($errorid, $message)
    {
        $this->pack([
            'errorid' => $errorid,
            'message' => $message
        ]);
        $this->deliver();
    }
}
