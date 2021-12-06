<?php

namespace Moxl\Xec\Action\PubsubSubscription;

use Moxl\Xec\Action\Pubsub\Errors;
use Moxl\Stanza\PubsubSubscription;

class Remove extends Errors
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

    public function handle($stanza, $parent = false)
    {
        if ($this->_pepnode == 'urn:xmpp:pubsub:movim-public-subscription') {
            \App\User::me()->subscriptions()
                           ->where('server', $this->_server)
                           ->where('node', $this->_node)
                           ->delete();
        }

        $this->pack(['server' => $this->_server, 'node' => $this->_node]);
        $this->deliver();
    }

    public function errorItemNotFound($stanza)
    {
        $this->handle($stanza, $parent = false);
    }

    public function errorUnexpectedRequest($stanza)
    {
        $this->handle($stanza, $parent = false);
    }
}
