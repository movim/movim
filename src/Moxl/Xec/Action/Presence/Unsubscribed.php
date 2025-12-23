<?php

namespace Moxl\Xec\Action\Presence;

use Moxl\Stanza\Presence;
use Moxl\Xec\Action;

class Unsubscribed extends Action
{
    protected $_to;

    public function request()
    {
        $this->store();
        $this->send(Presence::maker($this->me,
            to: $this->_to,
            type: 'unsubscribed'
        ));
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $this->pack($this->_to);
        $this->deliver();
    }
}
