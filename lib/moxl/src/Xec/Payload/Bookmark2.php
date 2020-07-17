<?php

namespace Moxl\Xec\Payload;

use App\Conference;

class Bookmark2 extends Payload
{
    public function handle($stanza, $parent = false)
    {
        if (current(explode('/', (string)$parent->attributes()->from)) != \App\User::me()->id) return;

        if ($stanza->items->retract) {
            \App\User::me()->session
                           ->conferences()
                           ->where('conference', (string)$stanza->items->retract->attributes()->id)
                           ->delete();
        } else {
            $conference = new Conference;

            $conference->conference     = (string)$stanza->items->item->attributes()->id;
            $conference->name           = (string)$stanza->items->item->conference->attributes()->name;
            $conference->nick           = (string)$stanza->items->item->conference->nick;
            $conference->autojoin       = filter_var($stanza->items->item->conference->attributes()->autojoin, FILTER_VALIDATE_BOOLEAN);

            \App\User::me()->session->conferences()->where('conference', $conference->conference)->delete();

            $conference->save();

            $this->pack($conference);
            $this->deliver();
        }
    }
}
