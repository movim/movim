<?php

namespace Moxl\Xec\Payload;

use App\Session;

class SASLFailure extends Payload
{
    public function handle($stanza, $parent = false)
    {
        Session::find(SESSION_ID)->delete();

        $this->pack($stanza->children()->getName());
        $this->deliver();
    }
}
