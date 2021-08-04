<?php

namespace Moxl\Xec\Action\Session;

use Moxl\Xec\Action;
use Moxl\Stanza\Stream;
use Movim\Session;

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

        $session = \App\User::me()->session;
        $session->active = true;
        $session->save();

        \Utils::info('/// AUTH SUCCESSFULL');
        fwrite(STDERR, 'started');
        $this->deliver();
    }
}
