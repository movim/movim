<?php

namespace Moxl\Xec\Action\OMEMO;

use Moxl\Xec\Action;
use Moxl\Stanza\OMEMO;

class SetDevicesList extends Action
{
    private array $_list;

    public function request()
    {
        $this->store();
        OMEMO::SetDevicesList(array_unique($this->_list));
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
