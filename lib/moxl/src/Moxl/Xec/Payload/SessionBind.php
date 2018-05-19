<?php

namespace Moxl\Xec\Payload;

use Moxl\Xec\Action\Session\Start;
use Moxl\Xec\Action\Session\Bind;

use Movim\Session;

class SessionBind extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $session = Session::start();

        $ss = new Bind;
        $ss->setResource($session->get('resource'))
           ->request();
    }
}
