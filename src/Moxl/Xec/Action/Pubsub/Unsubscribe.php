<?php

namespace Moxl\Xec\Action\Pubsub;

use Moxl\Xec\Action;
use Moxl\Stanza\Pubsub;
use Moxl\Xec\Action\Pubsub\Errors;
use Moxl\Xec\Action\PubsubSubscription\Remove as SubscriptionRemove;

class Unsubscribe extends Errors
{
    private $_to;
    private $_from;
    private $_node;
    private $_subid;

    public function request()
    {
        $this->store();
        Pubsub::unsubscribe($this->_to, $this->_from, $this->_node, $this->_subid);
    }

    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }

    public function setFrom($from)
    {
        $this->_from = $from;
        return $this;
    }

    public function setNode($node)
    {
        $this->_node = $node;
        return $this;
    }

    public function setSubid($subid)
    {
        $this->_subid = $subid;
        return $this;
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
