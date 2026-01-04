<?php

namespace Moxl\Xec\Action\Presence;

use Moxl\Stanza\Presence;
use Moxl\Xec\Action;

class Subscribe extends Action
{
    protected $_to;
    protected $_status;

    public function request()
    {
        $this->store();
        $this->send(Presence::maker($this->me,
            to: $this->_to,
            status: $this->_status,
            type: 'subscribe'
        ));
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $this->pack($this->_to);
        $this->deliver();
    }
}
