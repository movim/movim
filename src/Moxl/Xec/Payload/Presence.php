<?php
/*
 * @file Presence.php
 * 
 * @brief Handle incoming presences
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

class Presence extends Payload
{
    public function handle($stanza, $parent = false) {
        $evt = new \Event();
        
        // Subscribe request
        if((string)$stanza->attributes()->type == 'subscribe') {
            $notifs = \Cache::c('activenotifs');
            $notifs[(string)$stanza->attributes()->from] = 'sub';
            \Cache::c('activenotifs', $notifs);
            
            $evt->runEvent('subscribe', (string)$stanza->attributes()->from);
        } else {    
            $p = new \modl\Presence();
            $p->setPresence($stanza);
            $pd = new \modl\PresenceDAO();
            $pd->set($p);

            $this->pack($p);
            $this->deliver();
        }
    }
}
