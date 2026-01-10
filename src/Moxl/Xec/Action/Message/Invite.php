<?php

namespace Moxl\Xec\Action\Message;

use Moxl\Xec\Action;
use Moxl\Stanza\Message;

class Invite extends Action
{
    protected $_to;
    protected $_content;
    protected $_id;
    protected $_invite;

    public function request()
    {
        $messageId = $this->store($this->_id);

        $this->send(Message::maker(
            to: $this->_to,
            messageId: $messageId,
            id: $this->_id,
            invite: $this->_invite
        ));
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $this->deliver();
    }

    public function error(string $errorId, ?string $message = null)
    {
        if ($message) {
            $this->pack($message);
            $this->deliver();
        }
    }
}
