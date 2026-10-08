<?php

namespace Moxl\Xec\Action\Ping;

use Moxl\Stanza\Ping;
use Moxl\Xec\Action;

class Server extends Action
{
    public function request()
    {
        $this->store();
        Ping::entity();
    }
}
