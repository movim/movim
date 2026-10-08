<?php

namespace Moxl\Xec\Payload;

use App\Session as DBSession;
use Moxl\Xec\Action\Register\Get;

class Register extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $session = DBSession::find($this->me->session->id);

        if ($session && isset($session->username)) {
            $r = new Get($this->me);
            $r->request();
        }
    }
}
