<?php

namespace Moxl\Xec\Payload;

use Movim\User;

class MAMResult extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $user = new User;

        if ($stanza->forwarded->delay
            && (
                empty((string)$parent->attributes()->from)
                ||
                (string)$parent->attributes()->from == $user->getLogin()
            )
        ) {
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
