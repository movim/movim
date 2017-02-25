<?php

namespace Moxl\Xec\Action\Session;

use Moxl\Xec\Action;
use Moxl\Stanza\Stream;
use Moxl\Utils;

use Movim\Session;

class Bind extends Action
{
    private $_to;

    public function request()
    {
        $this->store();
        Stream::bindSet($this->_resource);
    }

    public function setResource($resource)
    {
        $this->_resource = $resource;
        return $this;
    }

    public function handle($stanza, $parent = false)
    {
        $session = Session::start();

        list($jid, $resource) = explode('/', (string)$stanza->bind->jid);

        list($username, $host) = explode('@',$jid);

        $session->set('username', $username);
        $session->set('host', $host);

        if($resource) {
            $session->set('resource', $resource);
        }

        $ss = new Start;
        $ss->setTo($session->get('host'))
           ->request();
    }
}
