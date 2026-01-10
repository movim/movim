<?php

namespace Moxl\Xec\Action\Presence;

use Moxl\Xec\Action;
use Moxl\Stanza\Presence;
use App\Presence as DBPresence;

class Chat extends Action
{
    protected $_status;

    public function request()
    {
        $this->store();
        $this->send(Presence::maker($this->me, status: $this->_status, show: 'chat'));
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $presence = DBPresence::findByStanza($this->me, $stanza);
        $presence->set($this->me, $stanza);

        linker($this->sessionId)->presenceBuffer->append($presence, function () {
            $this->event('mypresence');
        });
    }
}
