<?php
/*
 * Unavailable.php
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

class Unavailable extends Action
{
    private $_status;
    private $_to;
    private $_type;
    private $_resource;

    public function request() 
    {
        $this->store();
        Presence::unavailable($this->_to.'/'.$this->_resource, $this->_status, $this->_type);
    }

    public function setStatus($status)
    {
        $this->_status = $status;
        return $this;
    }
    
    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }
    
    public function setResource($resource)
    {
        $this->_resource = $resource;
        return $this;
    }
    
    public function setType($type)
    {
        $this->_type = $type;
        return $this;
    } 

    // Fixme ? For the moment this method is used only for the MUC requests
    public function handle($stanza, $parent = false) {
        $cd = new \modl\ConferenceDAO();
        $conf = $cd->get($this->_to);
        $conf->status = 0;
        $cd->set($conf);

        // We clear all the old messages
        $md = new \modl\MessageDAO();
        $md->deleteContact($this->_to);

        $md = new \modl\PresenceDAO();
        $md->clearMuc($this->_to);
        
        $evt = new \Event();
        $evt->runEvent('muc_presence', false);
    }
}
