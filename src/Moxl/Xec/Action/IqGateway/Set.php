<?php

namespace Moxl\Xec\Action\IqGateway;

use Moxl\Xec\Action;
use Moxl\Stanza\IqGateway;

class Set extends Action
{
    protected $_to;
    protected $_prompt;
    protected $_extra;

    public function request()
    {
        $this->store();
        $this->iq(IqGateway::set($this->_prompt), to: $this->_to, type: 'set');;
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $this->prepare($stanza, $parent);
        $this->pack([
            'query' => $stanza->query,
            'extra' => $this->_extra
        ]);
        $this->deliver();
    }

    public function error(string $errorId, ?string $message = null)
    {
        $this->pack([
            'errorid' => $errorId,
            'message' => $message
        ]);
        $this->deliver();
    }
}
