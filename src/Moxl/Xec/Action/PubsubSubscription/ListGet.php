<?php

namespace Moxl\Xec\Action\PubsubSubscription;

use Moxl\Xec\Action;
use Moxl\Xec\Action\Pubsub\Errors;
use Moxl\Stanza\PubsubSubscription;

class ListGet extends Errors
{
    
    public function request() 
    {
        $this->store();
        PubsubSubscription::listGet();
    }
    
    public function handle($stanza, $parent = false) {
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
        parent::errorItemNotFound($stanza);
        $evt = new \Event();
        $evt->runEvent('groupsubscribedlist', array()); 
    }
}
