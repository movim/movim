<?php
/*
 * DND.php
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

namespace Moxl\Xec\Action\Presence;

use Moxl\Xec\Action;
use Moxl\Stanza\Presence;

class DND extends Action
{
    private $_status;
    
    public function request() 
    {
        $this->store();
        Presence::DND($this->_status);
    }

    public function setStatus($status)
    {
        $this->_status = $status;
        return $this;
    }  
    
    public function handle($stanza) {
        $p = new \modl\Presence();
        $p->setPresence($stanza);
        
        $pd = new \modl\PresenceDAO();
        $pd->set($p);

        $evt = new \Event();
        $evt->runEvent('mypresence');
    }
}
