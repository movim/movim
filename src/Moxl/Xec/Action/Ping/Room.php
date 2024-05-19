<?php

namespace Moxl\Xec\Action\Ping;

use Movim\ChatroomPings;
use Moxl\Xec\Action;
use Moxl\Stanza\Ping;
use Rooms;
use SimpleXMLElement;

/**
 * XEP-0410: MUC Self-Ping (Schrödinger's Chat)
 */
class Room extends Action
{
    protected ?string $_room = null;
    protected ?string $_resource = null;

    public function request()
    {
        $this->store();
        Ping::entity($this->_resource);
    }

    public function handle(?SimpleXMLElement $stanza = null, ?SimpleXMLElement $parent = null)
    {
        ChatroomPings::getInstance()->touch($this->_room);
    }

    public function error(string $errorId, ?string $message = null)
    {
        (new Rooms())->ajaxExit($this->_room);
    }
}
