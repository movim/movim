<?php

namespace Moxl\Xec\Payload;

use App\Session;

class SASLFailure extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        Session::find($this->sessionId)->delete();

        $this->pack($stanza->children()->getName());
        $this->deliver();
    }
}
