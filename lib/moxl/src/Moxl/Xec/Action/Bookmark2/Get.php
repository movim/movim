<?php

namespace Moxl\Xec\Action\Bookmark2;

use App\Conference;
use Moxl\Xec\Action;
use Moxl\Stanza\Bookmark2;

class Get extends Action
{
    protected $_to;

    public function request()
    {
        $this->store();
        Bookmark2::get();
    }

    public function handle($stanza, $parent = false)
    {
        \App\User::me()->session->conferences()->delete();

        foreach ($stanza->pubsub->items->item as $c) {
            $conference = new Conference;

            $conference->conference     = (string)$c->attributes()->id;
            $conference->name           = (string)$c->conference->attributes()->name;
            $conference->nick           = (string)$c->conference->nick;
            $conference->autojoin       = filter_var($c->conference->attributes()->autojoin, FILTER_VALIDATE_BOOLEAN);

            $conference->save();
        }

        $this->deliver();
    }
}
