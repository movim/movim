<?php

namespace Moxl\Xec\Action\PubsubSubscription;

use Moxl\Xec\Action;
use Moxl\Stanza\PubsubSubscription;

class Add extends Action
{
    protected $_server;
    protected $_from;
    protected $_node;
    protected $_data = [];
    protected $_pepnode = 'urn:xmpp:pubsub:subscription';

    public function request()
    {
        $this->store();
        PubsubSubscription::listAdd(
            $this->_server,
            $this->_from,
            $this->_node,
            is_array($this->_data) && array_key_exists('title', $this->_data)
                ? $this->_data['title']
                : null,
            $this->_pepnode
        );
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $subscription = \App\Subscription::firstOrNew([
            'jid' => $this->_from,
            'server' => $this->_server,
            'node' => $this->_node
        ]);

        if ($this->_pepnode == 'urn:xmpp:pubsub:subscription') {
            $subscription->public = true;
        }

        $subscription->save();

        $this->deliver();
    }
}
