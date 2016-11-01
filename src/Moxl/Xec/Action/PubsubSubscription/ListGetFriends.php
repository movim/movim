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

    public function handle($stanza, $parent = false)
    {
        $tab = [];

        foreach($stanza->pubsub->items->children() as $i) {
            $sub = [
                (string)$i->subscription["node"],
                (string)$i->subscription["server"],
                (string)$i->subscription->title
            ];
            array_push($tab, $sub);
        }

        if(count($tab) == 0)
            $this->event('groupsubscribedlisterror', '');
        else
            $this->event('groupsubscribedlist', $tab);
    }

    public function errorFeatureNotImplemented($error)
    {
        $this->event('groupsubscribedlisterror', $error);
    }

    public function errorItemNotFound($error)
    {
        $this->event('groupsubscribedlisterror', $error);
    }
}

