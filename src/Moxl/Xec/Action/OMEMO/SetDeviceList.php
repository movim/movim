<?php

namespace Moxl\Xec\Action\OMEMO;

use Moxl\Xec\Action;
use Moxl\Stanza\OMEMO;

class SetDeviceList extends Action
{
    private array $_list;

    public function request()
    {
        $this->store();
        OMEMO::setDeviceList($this->_list);
    }

    public function setList(array $list)
    {
        $this->_list = $list;
        return $this;
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $this->deliver();
    }
}
