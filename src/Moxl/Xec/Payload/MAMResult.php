<?php

namespace Moxl\Xec\Payload;

use Movim\User;
use Movim\Session;

class MAMResult extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $user = new User;
        $session = Session::start();

        if ($stanza->forwarded->delay
        && isset($stanza->attributes()->queryid)
        && $session->get('mamid'.(string)$stanza->attributes()->queryid) == true) {
            $m = new \Modl\Message;
            $m->set($stanza->forwarded->message, $stanza->forwarded);

            if(!preg_match('#^\?OTR#', $m->body)) {
                $md = new \Modl\MessageDAO;
                $md->set($m);

                $this->pack($m);
                $this->deliver();
            }
        }
    }
}
