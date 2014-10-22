<?php
/*
 * @file Vcard4.php
 * 
 * @brief Handle incoming Vcard4 update
 * 
 * Copyright 2013 edhelas <edhelas@edhelas-laptop>
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

class Vcard4 extends Payload
{
    public function handle($stanza, $parent = false) {        
        $jid = current(explode('/',(string)$parent->attributes()->from));

        $evt = new \Event();
            
        $cd = new \modl\ContactDAO();
        
        $c = $cd->get($jid);
        
        if($c == null)
            $c = new \modl\Contact();

        $c->jid       = $jid;

        $vcard = $stanza->items->item->vcard;
        
        $c->setVcard4($vcard);

        $c->createThumbnails();

        $cd->set($c);
            
        $evt->runEvent('vcard', $c);
    }
}

