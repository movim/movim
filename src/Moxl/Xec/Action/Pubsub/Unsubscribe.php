<?php

namespace Moxl\Xec\Action\Pubsub;

use Moxl\Stanza\Pubsub;
use Moxl\Xec\Action;
use Moxl\Xec\Action\PubsubSubscription\Remove as SubscriptionRemove;

class Unsubscribe extends Action
{
    protected $_to;
    protected $_from;
    protected $_node;
    protected $_subid;

    public function request()
    {
        $this->store();
        $this->iq(Pubsub::unsubscribe($this->_from, $this->_node, $this->_subid), to: $this->_to, type: 'set');
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $sa = new SubscriptionRemove;
        $sa->setServer($this->_to)
           ->setNode($this->_node)
           ->setFrom($this->_from)
           ->setPEPNode('urn:xmpp:pubsub:movim-public-subscription')
           ->request();

        $this->pack(['server' => $this->_to, 'node' => $this->_node]);
        $this->deliver();
    }

    public function errorNotSubscribed(string $errorId, ?string $message = null)
    {
        $this->handle();
    }

    public function errorUnexpectedRequest(string $errorId, ?string $message = null)
    {
        $this->handle();
    }

    public function errorItemNotFound(string $errorId, ?string $message = null)
    {
        $this->handle();
    }
}
