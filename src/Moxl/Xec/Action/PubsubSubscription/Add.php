<?php

namespace Moxl\Xec\Action\PubsubSubscription;

use App\Subscription;
use Moxl\Xec\Action;
use Moxl\Stanza\PubsubSubscription;
use Moxl\Xec\Action\Pubsub\SetConfig;

class Add extends Action
{
    protected $_server;
    protected $_from;
    protected $_node;
    protected $_data = [];
    protected $_pepnode = Subscription::PUBLIC_NODE;
    // See https://github.com/processone/ejabberd/issues/3044#issuecomment-1605349858
    protected $_withPublishOption = true;

    protected ?string $_extensionsxml = null;
    protected ?int $_notify = null;
    protected ?bool $_pinned = false;

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
            $this->_withPublishOption,
            $this->_extensionsxml,
            $this->_notify,
            $this->_pinned
        ), type: 'set');
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $subscription = \App\Subscription::firstOrNew([
            'jid' => $this->_from,
            'server' => $this->_server,
            'node' => $this->_node
        ]);

        if ($this->_pepnode == Subscription::PUBLIC_NODE) {
            $subscription->public = true;
        }

        if ($this->_pepnode == Subscription::SPACE_NODE) {
            $subscription->space = true;
        }

        if ($this->_extensionsxml) {
            $subscription->extensions = $this->_extensionsxml;
        }

        $subscription->notify = $this->_notify;
        $subscription->pinned = $this->_pinned;
        $subscription->save();

        $this->pack(['server' => $this->_server, 'node' => $this->_node, 'data', $this->_data, 'type' => $this->_pepnode]);
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
        $config = new SetConfig($this->me, sessionId: $this->sessionId);
        $config->setNode($this->_pepnode)
               ->setData(PubsubSubscription::generateConfig($this->_pepnode))
               ->request();

        $this->_withPublishOption = false;
        $this->request();
    }
}
