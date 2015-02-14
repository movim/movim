<?php

namespace Moxl\Xec\Action\AdHoc;

use Moxl\Xec\Action;
use Moxl\Stanza\AdHoc;

class Submit extends Action
{
    private $_to;
    private $_node;
    private $_data;
    private $_sessionid;

    public function request() 
    {
        $this->store();
        AdHoc::submit($this->_to, $this->_node, $this->_data, $this->_sessionid);
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

    public function setData($data)
    {
        $this->_data = $data;
        return $this;
    }

    public function setSessionid($sessionid)
    {
        $this->_sessionid = $sessionid;
        return $this;
    }

    public function handle($stanza, $parent = false) {
        $this->pack($stanza->command);
        $this->deliver();
    }
}
