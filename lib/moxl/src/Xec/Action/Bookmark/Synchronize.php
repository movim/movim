<?php

namespace Moxl\Xec\Action\Bookmark;

use Moxl\Xec\Action;
use Moxl\Stanza\Bookmark;
use Moxl\Xec\Action\Bookmark2\Set;

/**
 * Synchronize Bookmark 1 to Bookmark 2
 */
class Synchronize extends Action
{
    protected $_to;

    public function request()
    {
        $this->store();
        Bookmark::get();
    }

    protected function saveItem($c)
    {
        $conference = new \App\Conference;

        $conference->conference     = (string)$c->attributes()->jid;
        $conference->name           = (string)$c->attributes()->name;
        $conference->nick           = (string)$c->nick;
        $conference->autojoin       = filter_var($c->attributes()->autojoin, FILTER_VALIDATE_BOOLEAN);

        $s = new Set;
        $s->setConference($conference)
          ->request();
    }

    public function handle($stanza, $parent = false)
    {
        if ($stanza->pubsub->items->item->storage) {
            $count = 0;

            if ($stanza->pubsub->items->item->count() == 1) {
                // We save the bookmarks as Subscriptions in the database
                foreach ($stanza->pubsub->items->item->storage->children() as $c) {
                    $count++;
                    $this->saveItem($c);
                }
            } else {
                // We parse non-standard XML where the items are in many <item>
                foreach ($stanza->pubsub->items->children() as $c) {
                    foreach ($c->storage->children() as $s) {
                        $count++;
                        $this->saveItem($s);
                    }
                }
            }

            $this->pack($count);
            $this->deliver();
        }
    }

    public function error($stanza, $parent = false)
    {
        $this->deliver();
    }
}
