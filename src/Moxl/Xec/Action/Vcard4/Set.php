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
        Vcard4::set($this->_data, $this->_withPublishOption);
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $this->pack(me()->id);
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
        $config = new SetConfig;
        $config->setNode(Vcard4::$node)
               ->setData(Vcard4::$nodeConfig)
               ->request();

        $this->_withPublishOption = false;
        $this->request();
    }
}
