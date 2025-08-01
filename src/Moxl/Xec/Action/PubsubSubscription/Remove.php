<?php

namespace Moxl\Xec\Action\PubsubSubscription;

use Moxl\Xec\Action;
use Moxl\Stanza\PubsubSubscription;

class Remove extends Action
{
    protected $_server;
    protected $_from;
    protected $_node;
    protected $_pepnode = 'urn:xmpp:pubsub:subscription';

    public function request()
    {
        $this->store();
        PubsubSubscription::listRemove(
            $this->_server,
            $this->_from,
            $this->_node,
            $this->_pepnode
        );
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        if ($this->_pepnode == 'urn:xmpp:pubsub:movim-public-subscription') {
            me()->subscriptions()
                           ->where('server', $this->_server)
                           ->where('node', $this->_node)
                           ->delete();
        }

        $this->pack(['server' => $this->_server, 'node' => $this->_node]);
        $this->deliver();
    }

    public function errorItemNotFound(string $errorId, ?string $message = null)
    {
        $this->handle();
    }

    public function errorUnexpectedRequest(string $errorId, ?string $message = null)
    {
        $this->handle();
    }
}
