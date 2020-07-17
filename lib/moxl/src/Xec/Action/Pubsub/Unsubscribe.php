<?php

namespace Moxl\Xec\Action\Pubsub;

use Moxl\Stanza\Pubsub;
use Moxl\Xec\Action\Pubsub\Errors;
use Moxl\Xec\Action\PubsubSubscription\Remove as SubscriptionRemove;

class Unsubscribe extends Errors
{
    protected $_to;
    protected $_from;
    protected $_node;
    protected $_subid;

    public function request()
    {
        $this->store();
        Pubsub::unsubscribe($this->_to, $this->_from, $this->_node, $this->_subid);
    }

    public function handle($stanza, $parent = false)
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

    public function errorNotSubscribed($stanza)
    {
        $this->handle($stanza, $parent = false);
    }

    public function errorUnexpectedRequest($stanza)
    {
        $this->handle($stanza, $parent = false);
    }

    public function errorItemNotFound($stanza)
    {
        $this->handle($stanza, $parent = false);
    }
}
