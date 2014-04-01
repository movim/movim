<?php

namespace Moxl\Xec\Action\PubsubSubscription;

use Moxl\Xec\Action;
use Moxl\Xec\Action\Pubsub\Errors;
use Moxl\Stanza;

class ListGet extends Errors
{
    
    public function request() 
    {
        $this->store();
        Stanza\pubsubSubscriptionListGet();
    }
    
    public function handle($stanza) {
        $evt = new \Event();
        
        $tab = array();
        foreach($stanza->pubsub->items->children() as $i) {
            $sub = array(
                'node'   => (string)$i->subscription["node"],
                'server' => (string)$i->subscription["server"],
                'title'  => (string)$i->subscription->title);

            $tab[(string)$i->subscription["server"].(string)$i->subscription["node"]] = $sub;
        }
        
        $evt->runEvent('groupsubscribedlist', $tab); 
    }

    public function errorItemNotFound($stanza) {
        $evt = new \Event();
        $evt->runEvent('groupsubscribedlist', array()); 
    }
}
