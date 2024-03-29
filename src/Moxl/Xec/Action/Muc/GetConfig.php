<?php

namespace Moxl\Xec\Action\Muc;

use Moxl\Xec\Action;
use Moxl\Stanza\Muc;

class GetConfig extends Action
{
    protected $_to;

    public function request()
    {
        $this->store();
        Muc::getConfig($this->_to);
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $this->pack(['config' => $stanza->query, 'room' => $this->_to]);
        $this->deliver();
    }
}
