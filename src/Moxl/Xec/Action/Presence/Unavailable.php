<?php

namespace Moxl\Xec\Action\Presence;

use App\Presence as DBPresence;
use App\PresenceBuffer;
use Moxl\Xec\Action;
use Moxl\Stanza\Presence;

class Unavailable extends Action
{
    protected $_status;
    protected $_to;
    protected $_type;
    protected $_resource;

    public function request()
    {
        $this->store();
        Presence::unavailable($this->_to . '/' . $this->_resource, $this->_status, $this->_type);
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $presence = DBPresence::findByStanza($stanza);
        $presence->set($stanza);

        PresenceBuffer::getInstance()->append($presence, function () {
            $this->pack($this->_to);
            $this->deliver();
        });

    }

    public function error(string $errorId, ?string $message = null)
    {
        $this->handle(null, null);
    }
}
