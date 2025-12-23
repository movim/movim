<?php

namespace Moxl\Xec\Action\Pubsub;

use Moxl\Stanza\Pubsub;
use Moxl\Xec\Action;

class GetSubscriptions extends Action
{
    protected $_to;
    protected $_node;
    protected $_notify = true;
    protected $_sync;

    public function request()
    {
        $this->store();
        $this->iq(Pubsub::getSubscriptions($this->_node), to: $this->_to, type: 'get');
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

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
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
