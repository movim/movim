<?php

namespace Moxl\Xec\Action\Pubsub;

use Moxl\Xec\Action\Pubsub\Errors;
use Moxl\Stanza\Pubsub;

class Delete extends Errors
{
    protected $_to;
    protected $_node;

    public function request()
    {
        $this->store();
        Pubsub::delete($this->_to, $this->_node);
    }

    public function handle($stanza, $parent = false)
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

    public function error($stanza, $parent = false)
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
