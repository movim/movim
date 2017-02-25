<?php

namespace Moxl\Xec\Action\Session;

use Moxl\Xec\Action;
use Moxl\Stanza\Stream;
use Moxl\Utils;

use Movim\Session;

class Start extends Action
{
    private $_to;

    public function request()
    {
        $this->store();
        Stream::sessionStart($this->_to);
    }

    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }

    public function handle($stanza, $parent = false)
    {
        $session = Session::start();
        $session->remove('password');
        $session->set('active', true);

        $sd = new \Modl\SessionxDAO;
        $session = $sd->get(SESSION_ID);
        $session->active = true;
        $sd->set($session);

        Utils::log("/// AUTH SUCCESSFULL");

        fwrite(STDERR, 'started');

        $this->pack($session);
        $this->deliver();
    }
}
