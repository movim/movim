<?php

namespace Moxl\Xec\Action\Avatar;

use Moxl\Xec\Action;
use Moxl\Stanza\Avatar;

class Set extends Action
{
    protected $_data;
    protected $_to = false;
    protected $_node = false;
    protected $_url = false;

    public function request()
    {
        $this->store();

        if ($this->_url === false) {
            Avatar::set($this->_data, $this->_to, $this->_node);
        }

        Avatar::setMetadata($this->_data, $this->_url, $this->_to, $this->_node);
    }

    public function handle($stanza, $parent = false)
    {
        if ($this->_to == false && $this->_node == false) {
            $this->pack(\App\User::me()->contact);
            $this->deliver();
        } else {
            $this->method('pubsub');
            $this->pack(['to' => $this->_to, 'node' => $this->_node]);
            $this->deliver();
        }
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
