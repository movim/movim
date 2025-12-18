<?php

namespace Moxl\Xec\Action\Register;

use Moxl\Xec\Action;
use Moxl\Stanza\Register;

class ChangePassword extends Action
{
    private $_to;
    private $_username;
    private $_password;

    public function request()
    {
        $this->store();
        $this->iq(Register::changePassword($this->_username, $this->_password), to: $this->_to, type: 'set');
    }

    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }

    public function setUsername($username)
    {
        $this->_username = $username;
        return $this;
    }

    public function setPassword($password)
    {
        $this->_password = $password;
        return $this;
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $this->deliver();
    }
}
