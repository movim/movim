<?php

namespace Moxl\Xec\Action\MAM;

use Moxl\Xec\Action;
use Moxl\Stanza\MAM;

class GetConfig extends Action
{
    public function request()
    {
        $this->store();
        $this->iq(MAM::getConfig(), type: 'get');
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $this->pack($stanza->prefs->attributes()->default);
        $this->deliver();
    }
}
