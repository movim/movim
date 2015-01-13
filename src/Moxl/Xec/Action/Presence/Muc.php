<?php
/*
 * Muc.php
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

class Muc extends Action
{
    private $_to;
    private $_nickname;
    
    public function request() 
    {
        $this->store();

        // We clear all the old messages
        $md = new \modl\MessageDAO();
        $md->deleteContact($this->_to);
        
        Presence::muc($this->_to, $this->_nickname);
    }

    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }  

    public function setNickname($nickname)
    {
        $this->_nickname = $nickname;
        return $this;
    }  
    
    public function handle($stanza, $parent = false) {
        $p = new \modl\Presence();
        $p->setPresence($stanza);
        
        $pd = new \modl\PresenceDAO();
        $pd->set($p);

        $cd = new \modl\ConferenceDAO();
        $conf = $cd->get($this->_to);
        $conf->status = 1;
        $cd->set($conf);

        $this->deliver();
    }

    public function errorNotAcceptable($stanza, $message) {
        $evt = new \Event();
        $evt->runEvent('bookmarkerror', $message);
    }
}
