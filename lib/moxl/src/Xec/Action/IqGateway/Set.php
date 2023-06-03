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
        IqGateway::set($this->_to, $this->_prompt);
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $this->prepare($stanza, $parent);
        $this->pack([
            'query' => $stanza->query,
            'prompt' => $this->_prompt,
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
