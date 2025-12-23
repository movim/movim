<?php

namespace Moxl\Xec\Payload;

use Moxl\Xec\Action\Register\Get;
use App\Session as DBSession;

class Register extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $session = DBSession::find($this->me->session->id);

        if ($session && isset($session->username)) {
            $r = new Get;
            $r->request();
        }
    }
}
