<?php
/*
 * Get.php
 *
 * Copyright 2012 edhelas <edhelas@edhelas-laptop>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 *
 *
 */

namespace Moxl\Xec\Action\Bookmark;

use Moxl\Xec\Action;
use Moxl\Stanza\Bookmark;

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
        $sd = new \Modl\SubscriptionDAO;
        $cd = new \Modl\ConferenceDAO;

        if($c->getName() == 'subscription') {
            $su = new \Modl\Subscription;

            $su->jid            = $this->_to;
            $su->server         = (string)$c->attributes()->server;
            $su->node           = (string)$c->attributes()->node;
            $su->subscription   = 'subscribed';
            $su->subid          = (string)$c->attributes()->subid;

            $sd->set($su);
        } elseif($c->getName() == 'conference') {
            $co = new \Modl\Conference;

            $co->jid            = $this->_to;
            $co->conference     = (string)$c->attributes()->jid;
            $co->name           = (string)$c->attributes()->name;
            $co->nick           = (string)$c->nick;
            $co->autojoin       = (int)$c->attributes()->autojoin;
            $co->status         = 0;

            $cd->set($co);
        }
    }

    public function handle($stanza, $parent = false)
    {
        if($stanza->pubsub->items->item->storage) {
            $sd = new \Modl\SubscriptionDAO;
            $cd = new \Modl\ConferenceDAO;

            // We clear the old Bookmarks
            $sd->delete();
            $cd->delete();

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
