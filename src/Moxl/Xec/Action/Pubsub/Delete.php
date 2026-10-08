<?php

namespace Moxl\Xec\Action\Pubsub;

use Moxl\Xec\Action;
use Moxl\Stanza\Pubsub;

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
            //delete from bookmark
            \App\Subscription::where('server', $this->_to)
                             ->where('node', $this->_node)
                             ->delete();

            //delete from info
            \App\Info::where('server', $this->_to)
                     ->where('node', $this->_node)
                     ->delete();

            $this->pack(['server' => $this->_to, 'node' => $this->_node]);
            $this->deliver();
        }
    }

    public function error(string $errorId, ?string $message = null)
    {
        //delete from bookmark
        \App\Subscription::where('server', $this->_to)
                         ->where('node', $this->_node)
                         ->delete();

        //delete from info
        \App\Info::where('server', $this->_to)
                 ->where('node', $this->_node)
                 ->delete();

        $this->pack(['server' => $this->_to, 'node' => $this->_node]);
        $this->deliver();
    }
}
