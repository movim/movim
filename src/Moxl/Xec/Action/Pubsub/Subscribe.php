<?php

namespace Moxl\Xec\Action\Pubsub;

use Moxl\Stanza\Pubsub;
use Moxl\Xec\Action;
use Moxl\Xec\Action\PubsubSubscription\Add as SubscriptionAdd;

class Subscribe extends Action
{
    protected $_to;
    protected $_from;
    protected $_node;
    protected $_data;

    public function request()
    {
        $this->store();
        $this->iq(Pubsub::subscribe($this->_from, $this->_node), to: $this->_to, type: 'set');
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $sa = new SubscriptionAdd;
        $sa->setServer($this->_to)
           ->setNode($this->_node)
           ->setFrom($this->_from)
           ->setPEPNode('urn:xmpp:pubsub:movim-public-subscription')
           ->request();

        $this->pack(['server' => $this->_to, 'node' => $this->_node, 'data', $this->_data]);
        $this->deliver();
    }

    public function errorPresenceSubscriptionRequired(string $errorId, ?string $message)
    {
        $this->pack(['server' => $this->_to, 'node' => $this->_node]);
        $this->deliver();
    }

    public function errorUnsupported(string $errorId, ?string $message = null)
    {
        $this->pack(['server' => $this->_to, 'node' => $this->_node]);
        $this->deliver();
    }

    public function error(string $errorId, ?string $message = null)
    {
        $this->pack(['server' => $this->_to, 'node' => $this->_node]);
        $this->deliver();
    }
}
