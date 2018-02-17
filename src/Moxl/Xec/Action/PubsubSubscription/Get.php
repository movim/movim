<?php

namespace Moxl\Xec\Action\PubsubSubscription;

use Moxl\Xec\Action;
use Moxl\Xec\Action\Pubsub\Errors;
use Moxl\Stanza\Pubsub;

class Get extends Errors
{
    private $_to;
    private $_pepnode = 'urn:xmpp:pubsub:subscription';

    public function request()
    {
        $this->store();
        Pubsub::getItems($this->_to, $this->_pepnode);
    }

    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }

    public function setPEPNode($pepnode)
    {
        $this->_pepnode = $pepnode;
        return $this;
    }

    public function handle($stanza, $parent = false)
    {
        if ($this->_pepnode == 'urn:xmpp:pubsub:movim-public-subscription') {
            $sd = new \Modl\SubscriptionDAO;
            $sd->delete();

            foreach($stanza->pubsub->items->children() as $i) {
                $su = new \Modl\Subscription;
                $su->jid            = $this->_to;
                $su->server         = (string)$i->subscription->attributes()->server;
                $su->node           = (string)$i->subscription->attributes()->node;
                $su->subscription   = 'subscribed';
                $sd->set($su);
            }
        } else {
            $sd = new \Modl\SharedSubscriptionDAO;
            $sd->deleteJid($this->_to);

            foreach($stanza->pubsub->items->children() as $i) {
                $s = new \Modl\SharedSubscription;
                $s->set($this->_to, $i->subscription);
                $sd->set($s);
            }
        }

        $this->pack($this->_to);
        $this->deliver();
    }

    public function errorFeatureNotImplemented($error)
    {
        $this->deliver();
    }

    public function errorItemNotFound($error)
    {
        $this->deliver();
    }
}

