<?php

namespace Moxl\Xec\Payload;

use Movim\Session;

class SASL2Success extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $memorySession = Session::instance();
        $memorySession->delete('password');

        $session = me()->session;

        $jid = explodeJid((string)$stanza->{'authorization-identifier'});

        $session->username = $jid['username'];
        $session->host = $jid['server'];
        $session->resource = $jid['resource'];
        $session->active = true;
        $session->type = 'bind2';
        $session->save();

        fwrite(STDERR, 'started');

        $this->deliver();
    }
}
