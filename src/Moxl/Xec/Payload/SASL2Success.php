<?php

namespace Moxl\Xec\Payload;

use Movim\Session;
use Movim\Widget\Wrapper;

class SASL2Success extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $memorySession = Session::instance();
        $memorySession->delete('password');

        $session = \App\Session::where('user_id', $this->me->id)->first();
        $jid = explodeJid((string)$stanza->{'authorization-identifier'});

        $session->username = $jid['username'];
        $session->host = $jid['server'];
        $session->resource = $jid['resource'];
        $session->active = true;
        $session->type = 'bind2';
        $session->save();

        $this->me->refresh();
        Wrapper::getInstance()->setUser($this->me);

        fwrite(STDERR, 'started');

        $this->deliver();
    }
}
