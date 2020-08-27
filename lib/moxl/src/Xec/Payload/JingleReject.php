<?php

namespace Moxl\Xec\Payload;

use Movim\Session;

class JingleReject extends Payload
{
    public function handle($stanza, $parent = false)
    {
        // We can only reject the current session
        $jingleSid = Session::start()->get('jingleSid');
        if ($jingleSid && (string)$stanza->attributes()->id != $jingleSid) return;

        $this->pack([
            'from' => (string)$parent->attributes()->from,
            'id' => (string)$stanza->attributes()->id
        ]);
        $this->deliver();
    }
}
