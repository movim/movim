<?php

namespace Moxl\Xec\Action\Pubsub;

use Moxl\Stanza\Pubsub;
use Moxl\Xec\Action\Pubsub\Errors;

class GetSubscriptions extends Errors
{
    protected $_to;
    protected $_node;
    protected $_notify = true;

    public function request()
    {
        $this->store();
        Pubsub::getSubscriptions($this->_to, $this->_node);
    }

    public function setNotify($notify)
    {
        $this->_notify = (bool)$notify;
        return $this;
    }

    public function setSync()
    {
        $this->_sync = true;
        return $this;
    }

    public function handle($stanza, $parent = false)
    {
        $tab = [];

        foreach ($stanza->pubsub->subscriptions->children() as $s) {
            $subscription = \App\Subscription::firstOrNew([
                'jid' => (string)$s->attributes()->jid,
                'server' => $this->_to,
                'node' => $this->_node
            ]);
            $subscription->save();

            $sub = [
                'jid' => (string)$s['jid'],
                'subscription' => (string)$s['subscription'],
                'subid' => (string)$s['subid']
            ];

            array_push($tab, $sub);
        }

        \App\Info::where('server', $this->_to)
                 ->where('node', $this->_node)
                 ->update(['occupants' => count($tab)]);

        if (empty($tab)) {
            \App\Subscription::where('server', $this->_to)
                             ->where('node', $this->_node)
                             ->delete();
        }

        $this->pack([
            'subscriptions' => $tab,
            'to' => $this->_to,
            'node' => $this->_node]);

        if ($this->_notify) {
            $this->deliver();
        }
    }
}
