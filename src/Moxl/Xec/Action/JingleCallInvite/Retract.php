<?php

namespace Moxl\Xec\Action\JingleCallInvite;

use App\Message;
use Moxl\Stanza\JingleCallInvite;
use Moxl\Xec\Action;
use SimpleXMLElement;

class Retract extends Action
{
    protected string $_to;
    protected string $_id;

    public function request()
    {
        $this->store();
        JingleCallInvite::retract($this->_to, $this->_id);
    }

    public function handle(?SimpleXMLElement $stanza = null, ?SimpleXMLElement $parent = null)
    {
        $message = Message::eventMessageFactory(
            'jingle',
            bareJid($this->_to),
            $this->_id
        );
        $message->type = 'muji_retract';
        $message->save();

        $this->pack($message);
        $this->event('muji_message');
    }
}
