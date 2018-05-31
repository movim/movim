<?php

namespace Moxl\Xec\Action\Session;

use Moxl\Xec\Action;
use Moxl\Stanza\Stream;
use Moxl\Utils;

use Movim\Session;
use App\Session as DBSession;

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
        $session = Session::start();

        list($jid, $resource) = explode('/', (string)$stanza->bind->jid);

        list($username, $host) = explode('@',$jid);

        $session->set('username', $username);
        $session->set('host', $host);

        if($resource) {
            $session->set('resource', $resource);

            /*$dbsession = DBSession::where('id', SESSION_ID)->first();
            $dbsession->resource = $resource;
            $dbsession->save();*/
        }

        $ss = new Start;
        $ss->setTo($session->get('host'))
           ->request();
    }
}
