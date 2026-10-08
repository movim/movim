<?php

namespace Moxl\Xec\Action\Pubsub;

use App\Info;
use App\Subscription;
use Moxl\Stanza\Pubsub;
use Moxl\Xec\Action;

class Delete extends Action
{
    protected $_to;

    protected $_node;

    public function request()
    {
        $this->store();
        $this->iq(Pubsub::delete($this->_node), to: $this->_to, type: 'set');
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        if ($stanza['type'] == 'result') {
            // delete from bookmark
            Subscription::where('server', $this->_to)
                ->where('node', $this->_node)
                ->delete();

            // delete from info
            Info::where('server', $this->_to)
                ->where('node', $this->_node)
                ->delete();

            $this->pack(['server' => $this->_to, 'node' => $this->_node]);
            $this->deliver();
        }
    }

    public function error(string $errorId, ?string $message = null)
    {
        // delete from bookmark
        Subscription::where('server', $this->_to)
            ->where('node', $this->_node)
            ->delete();

        // delete from info
        Info::where('server', $this->_to)
            ->where('node', $this->_node)
            ->delete();

        $this->pack(['server' => $this->_to, 'node' => $this->_node]);
        $this->deliver();
    }
}
