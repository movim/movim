<?php

namespace Moxl\Xec\Action\Muc;

use Moxl\Xec\Action;
use Moxl\Stanza\Muc;

class CreateGroupChat extends Action
{
    protected string $_to;
    protected string $_name;
    protected string $_nick;
    protected $_autojoin;
    protected $_pinned;
    protected $_notify;

    public function request()
    {
        $this->store();
        $this->iq(Muc::createGroupChat($this->_name), to: $this->_to, type: 'set');
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $this->pack([
            'jid' => $this->_to,
            'name' => $this->_name,
            'nick' => $this->_nick,
            'autojoin' => $this->_autojoin,
            'pinned' => $this->_pinned,
            'notify' => $this->_notify,
        ]);
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
