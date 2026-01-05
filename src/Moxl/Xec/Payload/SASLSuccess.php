<?php

namespace Moxl\Xec\Payload;

use Movim\Widget\Wrapper;

class SASLSuccess extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $this->deliver();

        $this->me->refresh();
        Wrapper::getInstance()->setUser($this->me);

        list($username, $host) = explode('@', $this->me->id);
        \Moxl\Stanza\Stream::init($host, $this->me->id);
    }
}
