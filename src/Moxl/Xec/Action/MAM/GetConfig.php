<?php

namespace Moxl\Xec\Action\MAM;

use Moxl\Xec\Action;
use Moxl\Stanza\MAM;

class GetConfig extends Action
{
    public function request()
    {
        $this->store();
        MAM::getConfig();
    }

    public function handle($stanza, $parent = false)
    {
        $this->pack($stanza->prefs->attributes()->default);
        $this->deliver();
    }
}
