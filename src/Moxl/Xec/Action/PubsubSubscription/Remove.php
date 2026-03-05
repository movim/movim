<?php

namespace Moxl\Xec\Action\PubsubSubscription;

use App\Subscription;
use Moxl\Xec\Action;
use Moxl\Stanza\PubsubSubscription;

class Remove extends Action
{
    protected $_server;
    protected $_from;
    protected $_node;
    protected $_pepnode = Subscription::PUBLIC_NODE;

    public function request()
    {
        $this->store();
        $this->iq(PubsubSubscription::listRemove(
            $this->_server,
            $this->_from,
            $this->_node,
            $this->_pepnode
        ), type: 'set');
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        if (
            $this->_pepnode == Subscription::PRIVATE_NODE
            || $this->_pepnode == Subscription::SPACE_NODE
        ) {
            $this->me->subscriptions()
                ->where('server', $this->_server)
                ->where('node', $this->_node)
                ->delete();
        }

        $this->pack(['server' => $this->_server, 'node' => $this->_node, 'type' => $this->_pepnode]);
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
