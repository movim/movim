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
        $jids = [];

        foreach ($stanza->pubsub->subscriptions->children() as $s) {
            $sub = [
                'jid' => (string)$s->attributes()->jid,
                'subscription' => (string)$s->attributes()->subscription,
                'subid' => (string)$s->attributes()->subid
            ];
            array_push($tab, $sub);
            $jids[(string)$s->attributes()->jid] = true;
        }

        \App\Info::where('server', $this->_to)
            ->where('node', $this->_node)
            ->update(['occupants' => count($tab)]);

        if (empty($tab)) {
            \App\Subscription::where('server', $this->_to)
                ->where('node', $this->_node)
                ->delete();
        } else {
            $existingJids = \App\Subscription::where('server', $this->_to)
                ->where('node', $this->_node)
                ->whereIn('jid', array_keys($jids))
                ->pluck('jid')
                ->toArray();

            $jidsToSave = array_diff(array_keys($jids), $existingJids);

            foreach ($jidsToSave as $jid) {
                $subscription = new \App\Subscription([
                    'jid' => (string)$jid,
                    'server' => $this->_to,
                    'node' => $this->_node
                ]);
                $subscription->save();
            }
        }

        $this->pack([
            'subscriptions' => $tab,
            'to' => $this->_to,
            'node' => $this->_node
        ]);

        if ($this->_notify) {
            $this->deliver();
        }
    }
}
