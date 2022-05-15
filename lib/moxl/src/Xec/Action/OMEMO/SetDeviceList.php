<?php

namespace Moxl\Xec\Action\OMEMO;

use Moxl\Xec\Action;
use Moxl\Stanza\OMEMO;

class SetDeviceList extends Action
{
    private $_list;

    public function request()
    {
        $this->store();
        OMEMO::setDeviceList($this->_list);
    }

    public function setList($list)
    {
        $this->_list = $list;
        return $this;
    }

    public function handle($stanza, $parent = false)
    {
        $this->deliver();
    }
}
