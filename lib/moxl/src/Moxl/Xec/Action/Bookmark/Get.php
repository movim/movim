<?php

namespace Moxl\Xec\Action\Bookmark;

use Moxl\Xec\Action;
use Moxl\Stanza\Bookmark;
use Moxl\Xec\Action\PubsubSubscription\Add as SubscriptionAdd;

class Get extends Action
{
    private $_to;

    public function request()
    {
        $this->store();
        Bookmark::get();
    }

    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }

    private function saveItem($c)
    {
        if($c->getName() == 'subscription') {
            /*
             * Old deprecated method, moving the subscriptions to
             * another PEP node
             */
            $a = new SubscriptionAdd;
            $a->setServer((string)$c->attributes()->server)
              ->setNode((string)$c->attributes()->node)
              ->setFrom($this->_to)
              ->setPEPNode('urn:xmpp:pubsub:movim-public-subscription')
              ->request();
        } elseif($c->getName() == 'conference') {
            $conference = new \App\Conference;

            $conference->conference     = (string)$c->attributes()->jid;
            $conference->name           = (string)$c->attributes()->name;
            $conference->nick           = (string)$c->nick;
            $conference->autojoin       = (int)$c->attributes()->autojoin;

            $conference->save();
        }
    }

    public function handle($stanza, $parent = false)
    {
        if($stanza->pubsub->items->item->storage) {
            \App\User::me()->session->conferences()->delete();

            if($stanza->pubsub->items->item->count() == 1) {
                // We save the bookmarks as Subscriptions in the database
                foreach($stanza->pubsub->items->item->storage->children() as $c) {
                    $this->saveItem($c);
                }
            } else {
                // We parse non-standard XML where the items are in many <item>
                foreach($stanza->pubsub->items->children() as $c) {
                    foreach($c->storage->children() as $s) {
                        $this->saveItem($s);
                    }
                }
            }

            $this->deliver();
        }
    }
}
