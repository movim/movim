<?php

namespace Moxl\Xec\Action\Presence;

use Moxl\Stanza\Presence;
use Moxl\Xec\Action;

class Subscribed extends Action
{
    protected $_to;

    public function request()
    {
        $this->store();
        $this->send(Presence::maker($this->me,
            to: $this->_to,
            type: 'subscribed'
        ));
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $this->pack($this->_to);
        $this->deliver();
    }
}
