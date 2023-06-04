<?php

namespace Moxl\Xec\Action\Ping;

use Moxl\Xec\Action;
use Moxl\Stanza\Ping;

class Server extends Action
{
    public function request()
    {
        $this->store();
        Ping::server();
    }
}
