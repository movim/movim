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
        Stream::bindSet($this->_resource);
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $session = me()->session;

        $jid = explodeJid((string)$stanza->bind->jid);

        $session->username = $jid['username'];
        $session->host = $jid['server'];
        $session->type = 'bind1';

        if ($jid['resource']) {
            $session->resource = $jid['resource'];
        }

        $session->save();

        $ss = new Start;
        $ss->setTo($session->host)
           ->request();
    }
}
