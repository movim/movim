<?php

namespace Moxl\Xec\Action\PubsubSubscription;

use Moxl\Xec\Action;
use Moxl\Xec\Action\Pubsub\Errors;
use Moxl\Stanza\PubsubSubscription;

class Add extends Errors
{
    private $_server;
    private $_from;
    private $_node;
    private $_data;
    private $_pepnode = 'urn:xmpp:pubsub:subscription';

    public function request()
    {
        $this->store();
        PubsubSubscription::listAdd(
            $this->_server, $this->_from, $this->_node,
            $this->_data['title'], $this->_pepnode
        );
    }

    public function setServer($server)
    {
        $this->_server = $server;
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

    public function setPEPNode($pepnode)
    {
        $this->_pepnode = $pepnode;
        return $this;
    }

    public function setData($data)
    {
        $this->_data = $data;
        return $this;
    }

    public function handle($stanza, $parent = false)
    {
        if ($this->_pepnode == 'urn:xmpp:pubsub:movim-public-subscription') {
            $sd = new \Modl\SubscriptionDAO;
            $su = new \Modl\Subscription;

            $su->jid            = $this->_from;
            $su->server         = $this->_server;
            $su->node           = $this->_node;
            $su->subscription   = 'subscribed';

            $sd->set($su);
        }

        $this->deliver();
    }
}
