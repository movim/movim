<?php

namespace Moxl\Xec\Action\Avatar;

use Moxl\Xec\Action;
use Moxl\Stanza\Avatar;

class Set extends Action
{
    private $_data;

    public function request()
    {
        $this->store();
        Avatar::set($this->_data);
        Avatar::setMetadata($this->_data);
    }

    public function setData($data)
    {
        $this->_data = $data;
        return $this;
    }

    public function handle($stanza, $parent = false)
    {
        $this->pack(\App\User::me()->contact);
        $this->deliver();
    }

    public function errorFeatureNotImplemented($stanza)
    {
        $this->deliver();
    }

    public function errorBadRequest($stanza)
    {
        $this->deliver();
    }

    public function errorNotAllowed($stanza)
    {
        $this->deliver();
    }
}
