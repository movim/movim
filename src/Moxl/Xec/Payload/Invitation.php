<?php

namespace Moxl\Xec\Payload;

use Movim\User;

class Invitation extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $m = new \Modl\Message;
        $user = new User;

        $m->session = $user->getLogin();
        $m->jidto = (string)$parent->attributes()->to;
        $m->jidfrom = current(explode('/',(string)$stanza->invite->attributes()->from));
        $m->subject = current(explode('/',(string)$parent->attributes()->from));
        $m->type = 'invitation';
        $m->published = gmdate('Y-m-d H:i:s');
        $m->body = (string)$parent->body;

        $md = new \Modl\MessageDAO;
        $md->set($m);

        $this->pack($m);
        $this->deliver();
    }
}
