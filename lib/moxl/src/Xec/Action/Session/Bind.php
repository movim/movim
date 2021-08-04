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

    public function handle($stanza, $parent = false)
    {
        $session = \App\User::me()->session;

        list($jid, $resource) = explode('/', (string)$stanza->bind->jid);

        list($username, $host) = explode('@', $jid);

        $session->username = $username;
        $session->host = $host;

        if ($resource) {
            $session->resource = $resource;
        }

        $session->save();

        $ss = new Start;
        $ss->setTo($session->host)
           ->request();
    }
}
