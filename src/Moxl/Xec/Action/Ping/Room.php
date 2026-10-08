<?php

namespace Moxl\Xec\Action\Ping;

use App\Widgets\Rooms\Rooms as WidgetRooms;
use Moxl\Xec\Action;
use Moxl\Stanza\Ping;
use SimpleXMLElement;

/**
 * XEP-0410: MUC Self-Ping (Schrödinger's Chat)
 */
class Room extends Action
{
    protected ?string $_to = null;
    protected ?string $_room = null;
    protected ?string $_resource = null;

    public function request()
    {
        $this->store();
        $this->iq(Ping::entity(), to: $this->_resource, type: 'get');
    }

    public function handle(?SimpleXMLElement $stanza = null, ?SimpleXMLElement $parent = null)
    {
        linker($this->sessionId)->chatroomPings->touch($this->_room);
    }

    public function error(string $errorId, ?string $message = null)
    {
        (new WidgetRooms($this->me, sessionId: $this->sessionId))->ajaxExit($this->_room);
    }
}
