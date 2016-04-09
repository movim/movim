<?php

namespace Moxl\Xec\Action\Ping;

use Moxl\Xec\Action;
use Moxl\Stanza\Ping;

class Ping extends Action
{
    public function request()
    {
        $this->store();
        Ping::server();
    }

    public function handle($stanza, $parent = false)
    {
    }
}
