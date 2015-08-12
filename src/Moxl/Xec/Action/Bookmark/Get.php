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

    private function saveItem($c) {
        $sd = new \modl\SubscriptionDAO();
        $cd = new \modl\ConferenceDAO();
        
        if($c->getName() == 'subscription') {
            $su = new \modl\Subscription();
            
            $su->jid            = $this->_to;
            $su->server         = (string)$c->attributes()->server;
            $su->node           = (string)$c->attributes()->node;
            $su->subscription   = 'subscribed';
            $su->subid          = (string)$c->attributes()->subid;
        
            $sd->set($su);
        } elseif($c->getName() == 'conference') {
            $co = new \modl\Conference();

            $co->jid            = $this->_to;
            $co->conference     = (string)$c->attributes()->jid;
            $co->name           = (string)$c->attributes()->name;
            $co->nick           = (string)$c->nick;
            $co->autojoin       = (int)$c->attributes()->autojoin;
            $co->status         = 0;
        
            $cd->set($co);                    
        }
    }
    
    public function handle($stanza, $parent = false) {
        if($stanza->pubsub->items->item->storage) {
            $sd = new \modl\SubscriptionDAO();
            $cd = new \modl\ConferenceDAO();

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

            // And we generate the "bookmark array"
            /*$arr = array();
            foreach($stanza->pubsub->items->item->storage->children() as $c) {
                $tmp = array();
                $tmp['type'] = $c->getName();
                foreach($c->attributes() as $key => $value)
                    $tmp[$key] = (string)$value;

                foreach($c as $key => $value)
                    $tmp[$key] = (string)$value;
                    
                array_push($arr, $tmp);
            }*/
            
            //$evt = new \Event();
            //$evt->runEvent('bookmark', false);
            $this->deliver();
        }
    }

    public function error($stanza) {
        $evt = new \Event();
        $evt->runEvent('bookmarkerror', t('Error')); 
    }

    public function errorItemNotFound($stanza) {
        $evt = new \Event();
        $evt->runEvent('bookmarkerror', t('Item Not Found')); 
    }
    
    public function errorFeatureNotImplemented($stanza) {
        $evt = new \Event();
        $evt->runEvent('bookmarkerror', '501 '.t('Feature Not Implemented')); 
    }
}
