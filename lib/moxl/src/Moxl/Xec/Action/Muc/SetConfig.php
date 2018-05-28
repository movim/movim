<?php

namespace Moxl\Xec\Action\Muc;

use Moxl\Xec\Action;
use Moxl\Stanza\Muc;

class SetConfig extends Action
{
    private $_to;
    private $_data;

    public function request()
    {
        $this->store();
        Muc::setConfig($this->_to, $this->_data);
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
        $this->deliver();
    }

    public function error($id, $message = false)
    {
        if ($message) {
            $this->pack($message);
            $this->deliver();
        }
    }
}
