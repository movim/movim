<?php

namespace Moxl\Xec\Payload;
use App\Session as DBSession;

class SASLFailure extends Payload
{
    public function handle($stanza, $parent = false)
    {
        DBSession::find(SESSION_ID)->delete();

        $this->pack($stanza->children()->getName());
        $this->deliver();
    }
}
