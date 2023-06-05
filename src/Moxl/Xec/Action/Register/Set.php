<?php

namespace Moxl\Xec\Action\Register;

use Moxl\Xec\Action;
use Moxl\Stanza\Register;

class Set extends Action
{
    protected $_to;
    protected $_data;

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

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $this->pack($this->_data);
        $this->deliver();
    }

    public function error(string $errorId, ?string $message = null)
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
