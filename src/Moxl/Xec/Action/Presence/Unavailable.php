<?php

namespace Moxl\Xec\Action\Presence;

use App\Presence as DBPresence;
use Moxl\Xec\Action;
use Moxl\Stanza\Presence;

class Unavailable extends Action
{
    protected $_status;
    protected $_to;
    protected $_resource;

    public function request()
    {
        $this->store();
        $this->send(Presence::maker($this->me,
            to: $this->_to . '/' . $this->_resource,
            status: $this->_status,
            type: 'unavailable'
        ));
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $presence = DBPresence::findByStanza($this->me, $stanza);
        $presence->set($this->me, $stanza);

        linker($this->sessionId)->presenceBuffer->append($presence, function () {
            $this->pack($this->_to);
            $this->deliver();
        });
    }

    public function error(string $errorId, ?string $message = null)
    {
    }
}
