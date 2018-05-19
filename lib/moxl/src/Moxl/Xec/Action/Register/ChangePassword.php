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
        Register::changePassword($this->_to, $this->_username, $this->_password);
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

    public function handle($stanza, $parent = false)
    {
        $this->deliver();
    }
}
