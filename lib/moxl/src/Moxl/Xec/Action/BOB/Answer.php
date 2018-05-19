<?php

namespace Moxl\Xec\Action\BOB;

use Moxl\Xec\Action;
use Moxl\Stanza\BOB;

class Answer extends Action
{
    private $_to;
    private $_base64;
    private $_cid;
    private $_type;
    private $_id;

    public function request() 
    {
        $this->store();
        BOB::answer($this->_to, $this->_id, $this->_cid, $this->_type, $this->_base64);
    }

    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }

    public function setBase64($base64)
    {
        $this->_base64 = $base64;
        return $this;
    }

    public function setCid($cid)
    {
        $this->_cid = $cid;
        return $this;
    }
    
    public function setType($type)
    {
        $this->_type = $type;
        return $this;
    }

    public function setId($id)
    {
        $this->_id = $id;
        return $this;
    }

    public function handle($stanza, $parent = false) {
    }
}
