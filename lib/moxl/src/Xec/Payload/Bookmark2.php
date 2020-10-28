<?php

namespace Moxl\Xec\Payload;

use App\Conference;

class Bookmark2 extends Payload
{
    public function handle($stanza, $parent = false)
    {
        if (current(explode('/', (string)$parent->attributes()->from)) != \App\User::me()->id
        || (string)$parent->attributes()->from == (string)$parent->attributes()->to) return;

        if ($stanza->items->retract) {
            \App\User::me()->session
                           ->conferences()
                           ->where('conference', (string)$stanza->items->retract->attributes()->id)
                           ->delete();
        } else {
            $conference = new Conference;
            $conference->set($stanza->items->item);

            \App\User::me()->session->conferences()->where('conference', $conference->conference)->delete();

            $conference->save();

            $this->pack($conference);
            $this->deliver();
        }
    }
}
