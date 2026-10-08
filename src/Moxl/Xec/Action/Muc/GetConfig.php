<?php

namespace Moxl\Xec\Action\Muc;

use Moxl\Stanza\Muc;
use Moxl\Xec\Action;

class GetConfig extends Action
{
    protected $_to;

    public function request()
    {
        $this->store();
        $this->iq(Muc::getConfig(), to: $this->_to, type: 'get');
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $this->pack(['config' => $stanza->query, 'room' => $this->_to]);
        $this->deliver();
    }
}
