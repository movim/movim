<?php

namespace Moxl\Xec\Action\Presence;

use App\Presence as DBPresence;
use App\PresenceBuffer;
use Moxl\Stanza\Presence;
use Moxl\Xec\Action;

class Away extends Action
{
    protected $_status;
    protected $_last;

    public function request()
    {
        $this->store();
        $this->send(Presence::maker($this->me, status: $this->_status, show: 'away', last: $this->_last));
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $presence = DBPresence::findByStanza($stanza);
        $presence->set($stanza);

        PresenceBuffer::getInstance($this->me)->append($presence, function () {
            $this->event('mypresence');
        });
    }
}
