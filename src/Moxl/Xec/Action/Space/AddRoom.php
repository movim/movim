<?php

namespace Moxl\Xec\Action\Space;

use App\Conference;
use Moxl\Stanza\Bookmark2;
use Moxl\Stanza\Space;
use Moxl\Xec\Action;
use Moxl\Xec\Action\Pubsub\SetConfig;

class AddRoom extends Action
{
    protected ?Conference $_conference = null;
    protected bool $_withPublishOption = true;

    public function request()
    {
        $this->store();
        $this->iq(Bookmark2::set(
            $this->_conference,
            node: $this->_conference->space_node,
            withPublishOption: $this->_withPublishOption,
            nodeConfig: Space::NODE_CONFIG,
        ), to: $this->_conference->space_server, type: 'set');
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $this->pack([
            'server' => $this->_conference->space_server,
            'node' => $this->_conference->space_node
        ]);
        $this->deliver();
    }

    public function errorPreconditionNotMet(string $errorId, ?string $message = null)
    {
        $this->errorConflict($errorId, $message);
    }

    public function errorResourceConstraint(string $errorId, ?string $message = null)
    {
        $this->errorConflict($errorId, $message);
    }

    public function errorConflict(string $errorId, ?string $message = null)
    {
        $config = new SetConfig($this->me, sessionId: $this->sessionId);
        $config->setTo($this->_conference->space_server)
            ->setNode($this->_conference->space_node)
            ->setData(Space::NODE_CONFIG)
            ->request();

        $this->_withPublishOption = false;
        $this->request();
    }
}
