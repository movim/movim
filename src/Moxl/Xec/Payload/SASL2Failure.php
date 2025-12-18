<?php

namespace Moxl\Xec\Payload;

use App\Session;

class SASL2Failure extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        Session::find($this->me->session->id)->delete();

        $this->pack($stanza->children()->getName());
        $this->deliver();
    }
}
