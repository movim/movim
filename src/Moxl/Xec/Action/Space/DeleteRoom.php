<?php

namespace Moxl\Xec\Action\Space;

use Moxl\Xec\Action;
use Moxl\Stanza\Pubsub;

class DeleteRoom extends Action
{
    protected ?string $_to = null;
    protected ?string $_node = null;
    protected ?string $_id = null;

    public function request()
    {
        $this->store();
        $this->iq(Pubsub::itemDelete($this->_node, $this->_id), to: $this->_to, type: 'set');
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $this->me->session->conferences()
            ->where('space_server', $this->_to)
            ->where('space_node', $this->_node)
            ->where('conference', $this->_id)
            ->delete();

        $this->pack(['server' => $this->_to, 'node' => $this->_node]);
        $this->deliver();
    }
}
