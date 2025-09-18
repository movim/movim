<?php

namespace Moxl\Xec\Action\Presence;

use Moxl\Xec\Action;
use Moxl\Stanza\Presence;
use App\Presence as DBPresence;
use App\PresenceBuffer;

class Away extends Action
{
    protected $_status;
    protected $_last;

    public function request()
    {
        $this->store();
        Presence::away($this->_status, $this->_last);
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $presence = DBPresence::findByStanza($stanza);
        $presence->set($stanza);

        PresenceBuffer::getInstance()->append($presence, function () {
            $this->event('mypresence');
        });
    }
}
