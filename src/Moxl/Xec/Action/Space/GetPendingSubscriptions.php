<?php

namespace Moxl\Xec\Action\Space;

use Moxl\Stanza\AdHoc;
use Moxl\Stanza\Pubsub;
use Moxl\Xec\Action;

class GetPendingSubscriptions extends Action
{
    protected $_to;
    protected $_node;

    public function request()
    {
        $this->store();
        $this->iq(AdHoc::submit(
            'http://jabber.org/protocol/pubsub#get-pending',
            ['pubsub#node' => $this->_node],
            \generateUUID(),
            'execute'
        ), to: $this->_to, type: 'set');
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $this->deliver();
    }
}
