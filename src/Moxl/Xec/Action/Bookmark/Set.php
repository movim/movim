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
use Moxl\Stanza;

class Set extends Action
{
    private $_to;
    private $_arr;
    
    public function request() 
    {
        $this->store();
        Bookmark::set($this->_arr);
    }
    
    public function setArr($arr)
    {
        $this->_arr = $arr;
        return $this;
    }

    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }
    
    public function handle($stanza) {
        $sd = new \modl\SubscriptionDAO();
        $cd = new \modl\ConferenceDAO();

        // We clear the old Bookmarks
        $sd->delete();
        $cd->delete();
        
        // We save the bookmarks as Subscriptions in the database
        foreach($this->_arr as $c) {
            if($c['type'] == 'subscription') {
                $su = new \modl\Subscription();
                
                $su->jid            = $this->_to;
                $su->server         = (string)$c['server'];
                $su->node           = (string)$c['node'];
                $su->subscription   = 'subscribed';
                $su->subid          = (string)$c['subid'];
            
                $sd->set($su);
            } elseif($c['type'] == 'conference') {
                $co = new \modl\Conference();

                $co->jid            = $this->_to;
                $co->conference     = (string)$c['jid'];
                $co->name           = (string)$c['name'];
                $co->nick           = (string)$c['nick'];
                $co->autojoin       = (int)$c['autojoin'];
                $co->status         = 0;
            
                $cd->set($co);                    
            }
        }
               
        $evt = new \Event();
        $evt->runEvent('bookmark', false);
    }
}
