<?php

namespace Moxl\Xec\Action\PubsubSubscription;

use Moxl\Xec\Action\Pubsub\Errors;
use Moxl\Stanza\Pubsub;
use App\Subscription;

class Get extends Errors
{
    protected $_to;
    protected $_pepnode = 'urn:xmpp:pubsub:subscription';

    public function request()
    {
        $this->store();
        Pubsub::getItems($this->_to, $this->_pepnode, 1000);
    }

    public function handle($stanza, $parent = false)
    {
        Subscription::where('jid', $this->_to)
                    ->where('public', ($this->_pepnode == 'urn:xmpp:pubsub:subscription'))
                    ->delete();

        $subscriptions = [];

        foreach ($stanza->pubsub->items->children() as $i) {
            $subscription = \App\Subscription::firstOrNew([
                'jid' => $this->_to,
                'server' => (string)$i->subscription->attributes()->server,
                'node' => (string)$i->subscription->attributes()->node,
                //'public' => ($this->_pepnode == 'urn:xmpp:pubsub:subscription')
            ]);

            $insertAsWell = false;

            if ($this->_pepnode == 'urn:xmpp:pubsub:subscription') {
                // Remove the private subscriptions to insert the public ones
                if ($subscription->exists && $subscription->public == false) {
                    $subscription->delete();
                    $insertAsWell = true;
                }

                $subscription->public = true;
            }

            if (!$subscription->exists || $insertAsWell) {
                array_push($subscriptions, $subscription->toArray());
            }
        }

        Subscription::saveMany($subscriptions);

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
