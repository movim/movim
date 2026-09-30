<?php

namespace Moxl\Xec\Action\Carbons;

use Moxl\Stanza\Carbons;
use Moxl\Xec\Action;

class Enable extends Action
{
    public function request()
    {
        $this->iq(Carbons::enable(), type: 'set');
    }
}
