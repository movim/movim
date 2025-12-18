<?php

namespace Moxl\Xec\Action\Pubsub;

use Moxl\Stanza\Pubsub;
use Moxl\Xec\Action;

class SetConfig extends Action
{
    protected $_to;
    protected string $_node;
    protected array $_data;

    public function request()
    {
        $this->store();
        $this->iq(Pubsub::setConfig($this->_node, $this->_data), to: $this->_to, type: 'set');
    }

    public function error(string $errorId, ?string $message = null)
    {
        $this->pack($message);
        $this->deliver();
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $this->deliver();
    }
}
