<?php

namespace Moxl\Xec\Action\Session;

use Moxl\Xec\Action;
use Moxl\Stanza\Stream;
use Moxl\Utils;

use Movim\Session;
use App\Session as DBSession;

class Start extends Action
{
    protected $_to;

    public function request()
    {
        $this->store();
        Stream::sessionStart($this->_to);
    }

    public function handle($stanza, $parent = false)
    {
        $session = Session::start();
        $session->remove('password');
        $session->set('active', true);

        $session = DBSession::find(SESSION_ID);

        if ($session) {
            $session->active = true;
            $session->save();
        }

        Utils::log("/// AUTH SUCCESSFULL");
        fwrite(STDERR, 'started');
        $this->deliver();
    }
}
