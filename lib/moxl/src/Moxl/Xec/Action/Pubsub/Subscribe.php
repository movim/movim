<?php

namespace Moxl\Xec\Action\Pubsub;

use Moxl\Xec\Action;
use Moxl\Stanza\Pubsub;
use Moxl\Xec\Action\Pubsub\Errors;
use Moxl\Xec\Action\PubsubSubscription\Add as SubscriptionAdd;

class Subscribe extends Errors
{
    private $_to;
    private $_from;
    private $_node;
    private $_data;

    public function request()
    {
        $this->store();
        Pubsub::subscribe($this->_to, $this->_from, $this->_node);
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

    public function setData($data)
    {
        $this->_data = $data;
        return $this;
    }

    public function handle($stanza, $parent = false)
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

    public function errorUnsupported($stanza)
    {
        $this->pack(['server' => $this->_to, 'node' => $this->_node]);
        $this->deliver();
    }

    public function error($stanza)
    {
        $this->pack(['server' => $this->_to, 'node' => $this->_node]);
        $this->deliver();
    }
}
