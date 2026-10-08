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
        $this->iq(OMEMO::setDevicesList(array_unique($this->_list)), type: 'set');
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
