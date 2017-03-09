<?php

namespace Moxl\Xec\Action\PubsubSubscription;

use Moxl\Xec\Action;
use Moxl\Xec\Action\Pubsub\Errors;
use Moxl\Stanza\Pubsub;

class Get extends Errors
{
    private $_to;

    public function request()
    {
        $this->store();
        Pubsub::getItems($this->_to, 'urn:xmpp:pubsub:subscription');
    }

    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }

    public function handle($stanza, $parent = false)
    {
        $sd = new \Modl\SharedSubscriptionDAO;
        $sd->deleteJid($this->_to);

        foreach($stanza->pubsub->items->children() as $i) {
            $s = new \Modl\SharedSubscription;
            $s->set($this->_to, $i->subscription);
            $sd->set($s);
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

