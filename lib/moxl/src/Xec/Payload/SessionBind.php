<?php

namespace Moxl\Xec\Payload;

use Moxl\Xec\Action\Session\Bind;

class SessionBind extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $ss = new Bind;
        $ss->setResource(\App\User::me()->session->resource)
           ->request();
    }
}
