<?php

namespace Moxl\Xec\Action\Bookmark2;

use App\Conference;
use Moxl\Xec\Action;
use Moxl\Stanza\Bookmark2;

class Get extends Action
{
    protected $_to;
    protected $_version = '1';

    public function request()
    {
        $this->store();
        Bookmark2::get($this->_version);
    }

    public function handle($stanza, $parent = false)
    {
        \App\User::me()
            ->session
            ->conferences()
            ->where('bookmarkversion', (int)$this->_version)
            ->delete();

        $conferences = [];

        foreach ($stanza->pubsub->items->item as $c) {
            $conference = new Conference;
            $conference->set($c);
            array_push($conferences, $conference->toArray());
        }

        Conference::saveMany($conferences);

        $this->pack($this->_version);
        $this->deliver();
    }
}
