<?php

namespace Moxl\Xec\Payload;

use Movim\Session;

class JingleProceed extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $id = (string)$stanza->attributes()->id;
        Session::start()->set('jingleSid', $id);

        $this->pack([
            'from' => (string)$parent->attributes()->from,
            'id' => $id
        ]);

        $this->deliver();
    }
}
