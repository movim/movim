<?php

namespace Moxl\Xec\Action\Presence;

use Moxl\Xec\Action;
use Moxl\Stanza\Presence;
use App\Presence as DBPresence;

class XA extends Action
{
    protected $_status;

    public function request()
    {
        $this->store();
        Presence::XA($this->_status);
    }

    public function handle($stanza, $parent = false)
    {
        $presence = DBPresence::findByStanza($stanza);
        $presence->set($stanza);
        $presence->save();

        $this->event('mypresence', $stanza);
    }
}
