<?php

namespace Moxl\Xec\Action\Presence;

use Moxl\Xec\Action;
use Moxl\Stanza\Presence;
use App\Presence as DBPresence;
use App\PresenceBuffer;

class Chat extends Action
{
    protected $_status;

    public function request()
    {
        $this->store();
        Presence::chat($this->_status);
    }

    public function handle($stanza, $parent = false)
    {
        $presence = DBPresence::findByStanza($stanza);
        $presence->set($stanza);

        PresenceBuffer::getInstance()->append($presence, function () use ($stanza) {
            $this->event('mypresence', $stanza);
        });
    }
}
