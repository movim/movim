<?php

namespace Moxl\Xec\Payload;

use Moxl\Xec\Action\Register\Get;
use App\Session as DBSession;

class Register extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $session = DBSession::find(SESSION_ID);

        if ($session && isset($session->username)) {
            $r = new Get;
            $r->request();
        }
    }
}
