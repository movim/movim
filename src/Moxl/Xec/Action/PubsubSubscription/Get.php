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
        $tab = [];

        foreach($stanza->pubsub->items->children() as $i) {
            $sub = [
                'node'      => (string)$i->subscription["node"],
                'server'    =>(string)$i->subscription["server"],
                'title'     => (string)$i->subscription->title
            ];
            array_push($tab, $sub);
        }

        $this->pack($tab);
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

