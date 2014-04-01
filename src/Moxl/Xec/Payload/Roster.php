<?php
/*
 * @file Roster.php
 * 
 * @brief Handle incoming roster request
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

namespace Moxl\Xec\Payload;

class Roster extends Payload
{
    public function handle($stanza, $parent = false) {
        if((string)$parent->attributes()->type == 'set') 
        {
            $evt = new \Event();            
            $rd = new \modl\RosterLinkDAO();
            
            $jid = current(explode('/',(string)$parent->query->item->attributes()->jid));
            
            if((string)$parent->query->item->attributes()->subscription == 'remove')
                $rd->delete($jid);
            else {
                $from = current(explode('/',(string)$parent->attributes()->from));
                $name = (string)$parent->query->item->attributes()->name;
                $subscription = (string)$parent->query->item->attributes()->subscription;
                $group = (string)$parent->query->item->group;
                
                // If not found, we create it
                $r = new \modl\RosterLink();
                    
                $r->key = $from;
                $r->jid = $jid;
                $r->groupname = $group;
                $r->rostername = $name;
                $r->rostersubscription = $subscription;

                $rd->delete($jid);
                $rd->set($r);
            }
            
            $evt->runEvent('roster');
        }
    }
}
