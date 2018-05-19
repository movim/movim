<?php

namespace Moxl\Xec\Action\Confirm;

use Moxl\Xec\Action;
use Moxl\Stanza\Confirm;

class Accept extends Action
{
    private $_to;
    private $_id;
    private $_url;
    private $_method;

    public function request()
    {
        $this->store();
        Confirm::answer($this->_to, $this->_id, $this->_url, $this->_method);
    }

    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }

    public function setId($id)
    {
        $this->_id = $id;
        return $this;
    }

    public function setUrl($url)
    {
        $this->_url = $url;
        return $this;
    }

    public function setMethod($method)
    {
        $this->_method = $method;
        return $this;
    }

    public function handle($stanza, $parent = false)
    {
    }
}
