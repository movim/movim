<?php

namespace Moxl\Xec\Action\Space;

use Moxl\Stanza\Space;
use Moxl\Xec\Action;

class SetSubscription extends Action
{
    protected $_to;
    protected $_node;
    protected $_jid;
    protected $_subscription;

    public function request()
    {
        $this->store();
        $this->iq(Space::setSubscription(
            $this->_node,
            $this->_jid,
            $this->_subscription
        ), to: $this->_to, type: 'set');
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $this->pack([
            'server' => $this->_to,
            'node' => $this->_node,
            'jid' => $this->_jid,
            'subscription' => $this->_subscription
        ]);
        $this->deliver();
    }
}
