<?php

namespace Moxl\Xec\Action\Vcard4;

use Moxl\Xec\Action;
use Moxl\Stanza\Vcard4;
use Moxl\Xec\Action\Pubsub\SetConfig;

class Set extends Action
{
    protected $_data;
    protected bool $_withPublishOption = true;

    public function request()
    {
        $this->store();
        $this->iq(Vcard4::set($this->_data, $this->_withPublishOption), type: 'set');
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $this->pack($this->me->id);
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
        $config = new SetConfig($this->me);
        $config->setNode(Vcard4::$node)
               ->setData(Vcard4::$nodeConfig)
               ->request();

        $this->_withPublishOption = false;
        $this->request();
    }
}
