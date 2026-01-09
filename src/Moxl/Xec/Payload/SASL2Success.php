<?php

namespace Moxl\Xec\Payload;

class SASL2Success extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        linker($this->sessionId)->authentication->clear();

        $session = \App\Session::where('user_id', $this->me->id)->first();
        $jid = explodeJid((string)$stanza->{'authorization-identifier'});

        $session->username = $jid['username'];
        $session->host = $jid['server'];
        $session->resource = $jid['resource'];
        $session->active = true;
        $session->type = 'bind2';
        $session->save();

        $this->me->refresh();

        linker($this->sessionId)->attachUser($this->me);

        fwrite(STDERR, 'started');

        $this->deliver();
    }
}
