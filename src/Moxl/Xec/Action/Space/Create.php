<?php

namespace Moxl\Xec\Action\Space;

use Moxl\Xec\Action;
use Moxl\Stanza\Space;

class Create extends Action
{
    protected $_to;
    protected $_node;
    protected $_title;

    public function request()
    {
        $this->store();
        $this->iq(Space::create($this->_node, $this->_title), to: $this->_to, type: 'set');
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $this->pack(['server' => $this->_to, 'node' => $this->_node]);
        $this->deliver();
    }
}
