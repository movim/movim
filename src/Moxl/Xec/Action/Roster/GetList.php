<?php
/*
 * GetList.php
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

namespace Moxl\Xec\Action\Roster;

use Moxl\Xec\Action;
use Moxl\Stanza\Roster;

class GetList extends Action
{
    private $_from;
    
    public function request() 
    {
        $this->store();
        Roster::get();
    }
        
    public function setFrom($from)
    {
        $this->_from = $from;
        return $this;
    }
    
    public function handle($stanza) {        
        $evt = new \Event();
        
        // We get all our contact
        
        $rd = new \modl\RosterLinkDAO();
        $data = $rd->getRoster($this->_from);
        
        foreach($stanza->query->item as $item) {
            // We search if the contact exist in the database 
            $found = false;
            
            foreach($data as $cd) {
                if($cd->jid == (string)$item->attributes()->jid) {
                    $found = $cd;
                    break;
                }
            } 
            
            // If not found, we create it
            if($found == false) {
                $r = new \modl\RosterLink();
            } else {
                $r = $found;
            }
            
            $item->key = $this->_from;
            $r->set($item);
            
            if($found == false) {
                $rd->set($r);
            } else {
                $rd->update($r);
            }
        }
        
        $evt->runEvent('roster');
    }
    
    public function load($key) {}
    public function save() {}
}
