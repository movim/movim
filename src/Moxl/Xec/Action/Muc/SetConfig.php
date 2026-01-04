<?php

namespace Moxl\Xec\Action\Muc;

use Moxl\Xec\Action;
use Moxl\Stanza\Muc;

class SetConfig extends Action
{
    protected $_to;
    protected $_data;

    public function request()
    {
        $this->store();
        $this->iq(Muc::setConfig($this->_data), to: $this->_to, type: 'set');
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $this->pack($this->_to);
        $this->deliver();
    }

    public function error(string $errorId, ?string $message = null)
    {
        if ($message) {
            $this->pack($message);
            $this->deliver();
        }
    }
}
