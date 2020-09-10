<?php

namespace Moxl\Xec\Action\Register;

use Moxl\Xec\Action;
use Moxl\Stanza\Register;

class Remove extends Action
{
    public function request()
    {
        $this->store();
        Register::remove();
    }

    public function handle($stanza, $parent = false)
    {
        $this->deliver();
    }

    public function error($errorid, $message)
    {
        $this->pack($message);
        $this->deliver();
    }
}
