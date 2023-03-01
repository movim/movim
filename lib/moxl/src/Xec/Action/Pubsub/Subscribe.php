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
        Pubsub::subscribe($this->_to, $this->_from, $this->_node);
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
