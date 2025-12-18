<?php

namespace Moxl\Xec\Action\AdHoc;

use Moxl\Xec\Action;
use Moxl\Stanza\AdHoc;

class Command extends Action
{
    protected $_to;
    protected $_node;

    public function request()
    {
        $this->store();
        $this->iq(Adhoc::command($this->_node), to: $this->_to, type: 'set');
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $this->prepare($stanza, $parent);
        $this->pack($stanza->command);
        $this->deliver();
    }

    public function error(string $errorId, ?string $message = null)
    {
        $this->pack([
            'errorid' => $errorId,
            'message' => $message
        ]);
        $this->deliver();
    }
}
