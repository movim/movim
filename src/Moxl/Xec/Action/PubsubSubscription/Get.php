<?php

namespace Moxl\Xec\Action\PubsubSubscription;

use Moxl\Xec\Action;
use Moxl\Stanza\Pubsub;
use App\Subscription;

class Get extends Action
{
    protected $_to;
    protected $_pepnode = Subscription::PUBLIC_NODE;

    public function request()
    {
        $this->store();
        $this->iq(Pubsub::getItems($this->_pepnode, 1000), to: $this->_to, type: 'get');
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        Subscription::where('jid', $this->_to)
            ->where('public', ($this->_pepnode == Subscription::PUBLIC_NODE))
            ->where('space', false)
            ->delete();

        $subscriptions = [];

        foreach ($stanza->pubsub->items->children() as $i) {

            $subscription = Subscription::firstOrNew([
                'jid' => $this->_to,
                'server' => (string)$i->subscription->attributes()->server,
                'node' => (string)$i->subscription->attributes()->node
            ]);

            $insertAsWell = false;

            if ($this->_pepnode == Subscription::PUBLIC_NODE) {
                // Remove the private subscriptions to insert the public ones
                if ($subscription->exists && $subscription->public == false) {
                    Subscription::where(function ($query) use ($subscription) {
                        $query->where('jid', $subscription->jid)
                            ->where('server', $subscription->server)
                            ->where('node', $subscription->node);
                    })->where('space', false)->delete();

                    $insertAsWell = true;
                }

                $subscription->public = true;
            }

            if ($this->_pepnode == Subscription::SPACE_NODE) {
                $subscription->space = true;
            }

            $subscription->setExtensions($i->subscription->extensions);

            if (!$subscription->exists || $insertAsWell) {
                array_push($subscriptions, $subscription->toArray());
            }
        }

        Subscription::saveMany($subscriptions);

        $this->pack(['to' => $this->_to, 'node' => $this->_pepnode, 'type' => $this->_pepnode]);
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
