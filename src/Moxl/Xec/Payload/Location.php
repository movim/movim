<?php
/*
 * @file Location.php
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

namespace Moxl;

class Location extends Payload
{
    public function handle($stanza, $parent = false) {
        $to = current(explode('/',(string)$parent->attributes()->to));
        $from = current(explode('/',(string)$parent->attributes()->from));
        
        if(isset($stanza->items->item->geoloc) && $stanza->items->item->geoloc->count() > 0) {
            $cd = new \modl\ContactDAO();
            $c = $cd->get($from);
            
            if($c != null) {
                $c->setLocation($stanza);
                $cd->set($c);
            }
        }
    }
}
