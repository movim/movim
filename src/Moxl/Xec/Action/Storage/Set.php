<?php

namespace Moxl\Xec\Action\Storage;

use Moxl\Xec\Action;
use Moxl\Stanza\Storage;
use Moxl\Xec\Action\Pubsub\SetConfig;

class Set extends Action
{
    protected array $_data;
    protected bool $_withPublishOption = true;

    public function request()
    {
        $this->store();
        $this->iq(Storage::publish($this->_data, $this->_withPublishOption), type: 'set');
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $this->pack($this->_data);
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
        $config->setNode(Storage::$node)
               ->setData(Storage::$nodeConfig)
               ->request();

        $this->_withPublishOption = false;
        $this->request();
    }
}
