<?php

namespace Moxl\Xec\Payload;

use Movim\Session;

class SASLSuccess extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $session = Session::start();
        \Moxl\Stanza\Stream::init($session->get('host'));
    }
}
