<?php

namespace Moxl\Xec\Action\Register;

use Moxl\Xec\Action;
use Moxl\Stanza\Register;

class Set extends Action
{
    private $_to;
    private $_data;

    public function request()
    {
        $this->store();
        Register::set($this->_to, $this->_data);
    }

    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }

    public function setData($data)
    {
        $this->_data = $data;
        return $this;
    }

    public function handle($stanza, $parent = false)
    {
        $this->pack($this->_data);
        $this->deliver();
    }

    public function error($id, $message = false)
    {
        $this->pack($message);
        $this->deliver();
    }

    public function errorConflict($id, $message = false)
    {
        $this->pack($message);
        $this->deliver();
    }

    public function errorNotAcceptable($id, $message = false)
    {
        $this->deliver();
    }

    public function errorForbidden($id, $message = false)
    {
        $this->deliver();
    }
}
