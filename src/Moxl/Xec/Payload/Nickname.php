<?php

namespace Moxl\Xec\Payload;

class Nickname extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $to = current(explode('/',(string)$parent->attributes()->to));
        $from = current(explode('/',(string)$parent->attributes()->from));

        if($stanza->items->item->nick) {
            $cd = new \Modl\ContactDAO;
            $c = $cd->get($from);

            if($c == null) {
                $c = new \Modl\Contact;
                $c->jid = $from;
            }

            $c->nickname = (string)$stanza->items->item->nick;
            $cd->set($c);

        }
    }
}
