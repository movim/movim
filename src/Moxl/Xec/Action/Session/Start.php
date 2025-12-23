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
        $this->iq(Stream::sessionStart(), to: $this->_to, type: 'set');
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $session = Session::instance();
        $session->delete('password');

        $session = $this->me->session;
        $session->active = true;
        $session->save();

        fwrite(STDERR, 'started');

        $this->deliver();
    }
}
