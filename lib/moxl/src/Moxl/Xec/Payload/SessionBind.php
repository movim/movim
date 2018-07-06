<?php

namespace Moxl\Xec\Payload;

use Moxl\Xec\Action\Session\Bind;

class SessionBind extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $ss = new Bind;
        $ss->setResource(\App\User::me()->session->resource)
           ->request();
    }
}
