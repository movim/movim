<?php

namespace Moxl\Xec\Action\JingleCallInvite;

use Moxl\Stanza\JingleCallInvite;
use Moxl\Xec\Action;
use Moxl\Xec\Payload\CallInvitePropose;

class Invite extends Action
{
    protected string $_to;
    protected string $_id;
    protected ?string $_room = null;
    protected bool $_video = false;

    public function request()
    {
        $this->store();
        JingleCallInvite::invite($this->_to, $this->_id, $this->_room, $this->_video);
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $propose = new CallInvitePropose;
        $propose->handle($stanza->invite, $stanza);
    }

    public function enableVideo()
    {
        $this->_video = true;
        return $this;
    }
}
