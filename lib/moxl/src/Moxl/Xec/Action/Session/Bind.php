<?php

namespace Moxl\Xec\Action\Session;

use Moxl\Xec\Action;
use Moxl\Stanza\Stream;
use Moxl\Utils;

class Bind extends Action
{
    protected $_resource;

    public function request()
    {
        $this->store();
        Stream::bindSet($this->_resource);
    }

    public function handle($stanza, $parent = false)
    {
        $session = \App\User::me()->session;

        $jidParts = explodeJid((string)$stanza->bind->jid);

        $session->username = $jidParts['username'];
        $session->host = $jidParts['server'];

        if ($jidParts['resource']) {
            $session->resource = $jidParts['resource'];
        }

        $session->save();

        $ss = new Start;
        $ss->setTo($session->host)
           ->request();
    }
}
