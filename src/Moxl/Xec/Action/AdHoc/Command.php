<?php

namespace Moxl\Xec\Action\AdHoc;

use Moxl\Xec\Action;
use Moxl\Stanza\AdHoc;

class Command extends Action
{
    private $_to;
    private $_node;

    public function request() 
    {
        $this->store();
        AdHoc::command($this->_to, $this->_node);
    }

    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }

    public function setNode($node)
    {
        $this->_node = $node;
        return $this;
    }

    public function handle($stanza, $parent = false) {
        $this->prepare($stanza, $parent);
        $this->pack($stanza->command, $stanza->from);
        $this->deliver();
    }

    public function error($errorid, $message)
    {
        $this->pack([
            "errorid" => $errorid,
            "message" => $message
        ]);
        $this->deliver();
    }
}
