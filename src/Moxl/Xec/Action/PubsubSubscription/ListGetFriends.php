<?php

namespace Moxl\Xec\Action\PubsubSubscription;

use Moxl\Xec\Action;
use Moxl\Xec\Action\Pubsub\Errors;
use Moxl\Stanza\PubsubSubscription;

class ListGetFriends extends Errors
{
    private $_to;
    
    public function request() 
    {
        $this->store();
        PubsubSubscription::listGetFriends($this->_to);
    }
    
    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }
    
    public function handle($stanza, $parent = false) {
        $evt = new \Event();
        $tab = array();
        foreach($stanza->pubsub->items->children() as $i) {
            $sub = array((string)$i->subscription["node"], (string)$i->subscription["server"], (string)$i->subscription->title);
            array_push($tab, $sub);
        }
    
        if(count($tab) == 0)
            $evt->runEvent('groupsubscribedlisterror', ''); 
        else
            $evt->runEvent('groupsubscribedlist', $tab); 
    }

    public function errorFeatureNotImplemented($error) {
        $evt = new \Event();
        $evt->runEvent('groupsubscribedlisterror', $error); 
    }

    public function errorItemNotFound($error) {
        $evt = new \Event();
        $evt->runEvent('groupsubscribedlisterror', $error); 
    }
}


