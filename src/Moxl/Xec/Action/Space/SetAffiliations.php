<?php

namespace Moxl\Xec\Action\Space;

use Moxl\Stanza\Pubsub;
use Moxl\Xec\Action;

class SetAffiliations extends Action
{
    protected $_to;
    protected $_node;
    protected $_data;

    public function request()
    {
        $this->store();
        $this->iq(Pubsub::setAffiliations($this->_node, $this->_data), to: $this->_to, type: 'set');
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $this->pack([
            'server' => $this->_to,
            'node' => $this->_node,
            'data' => $this->_data
        ]);
        $this->deliver();
    }
}
