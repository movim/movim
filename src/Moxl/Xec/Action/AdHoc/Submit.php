<?php

namespace Moxl\Xec\Action\AdHoc;

use Moxl\Xec\Action;
use Moxl\Stanza\AdHoc;

class Submit extends Action
{
    protected $_to;
    protected $_node;
    protected $_data;
    protected $_sessionid;

    public function request()
    {
        $this->store();
        $this->iq(Adhoc::submit($this->_node, $this->_data, $this->_sessionid), to: $this->_to, type: 'set');
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
