<?php

namespace Moxl\Xec\Action\PubsubSubscription;

use Moxl\Xec\Action;
use Moxl\Stanza\PubsubSubscription;
use Moxl\Xec\Action\Pubsub\SetConfig;

class Add extends Action
{
    protected $_server;
    protected $_from;
    protected $_node;
    protected $_data = [];
    protected $_pepnode = 'urn:xmpp:pubsub:subscription';
    // See https://github.com/processone/ejabberd/issues/3044#issuecomment-1605349858
    protected $_withPublishOption = true;

    public function request()
    {
        $this->store();
        $this->iq(PubsubSubscription::listAdd(
            $this->_server,
            $this->_from,
            $this->_node,
            is_array($this->_data) && array_key_exists('title', $this->_data)
                ? $this->_data['title']
                : null,
            $this->_pepnode,
            $this->_withPublishOption
        ), type: 'set');
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

        $this->pack(['server' => $this->_server, 'node' => $this->_node, 'data', $this->_data]);
        $this->deliver();
    }

    public function errorPreconditionNotMet(string $errorId, ?string $message = null)
    {
        $this->errorConflict($errorId, $message);
    }

    public function errorResourceConstraint(string $errorId, ?string $message = null)
    {
        $this->errorConflict($errorId, $message);
    }

    public function errorConflict(string $errorId, ?string $message = null)
    {
        $config = new SetConfig($this->me);
        $config->setNode($this->_pepnode)
               ->setData(PubsubSubscription::generateConfig($this->_pepnode))
               ->request();

        $this->_withPublishOption = false;
        $this->request();
    }
}
