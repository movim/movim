<?php

namespace Moxl\Xec\Action\Pubsub;

use Moxl\Stanza\Pubsub;
use Moxl\Xec\Action\Pubsub\Errors;

class SetSubscriptions extends Errors
{
    protected $_to;
    protected $_node;
    protected $_data;

    public function request()
    {
        $this->store();
        Pubsub::setSubscriptions($this->_to, $this->_node, $this->_data);
    }

    public function handle($stanza, $parent = false)
    {
        $this->event('pubsubsubscriptionssubmited', $stanza);
    }
}
