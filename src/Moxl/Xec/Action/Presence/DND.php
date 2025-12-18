<?php

namespace Moxl\Xec\Action\Presence;

use App\Presence as DBPresence;
use App\PresenceBuffer;
use Moxl\Stanza\Presence;
use Moxl\Xec\Action;

class DND extends Action
{
    protected $_status;

    public function request()
    {
        $this->store();
        $this->send(Presence::maker($this->me, status: $this->_status, show: 'dnd'));
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $presence = DBPresence::findByStanza($this->me, $stanza);
        $presence->set($this->me, $stanza);

        PresenceBuffer::getInstance($this->me)->append($presence, function () {
            $this->event('mypresence');
        });
    }
}
