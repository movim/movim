<?php

namespace Moxl\Xec\Action\Session;

use Moxl\Xec\Action;
use Moxl\Stanza\Stream;

class Bind extends Action
{
    protected $_resource;

    public function request()
    {
        $this->store();
        $this->iq(Stream::bindSet($this->_resource), type: 'set');
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $session = $this->me->session;

        $jid = explodeJid((string)$stanza->bind->jid);

        $session->username = $jid['username'];
        $session->host = $jid['server'];
        $session->type = 'bind1';

        if ($jid['resource']) {
            $session->resource = $jid['resource'];
        }

        $session->save();

        $ss = new Start($this->me, sessionId: $this->sessionId);
        $ss->setTo($session->host)
           ->request();
    }
}
