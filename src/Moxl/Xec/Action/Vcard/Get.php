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

namespace Moxl\Xec\Action\Vcard;

use Moxl\Xec\Action;
use Moxl\Stanza;

class Get extends Action
{
    private $_to;
    private $_me = false;
    
    public function request() 
    {
        $this->store();
        Stanza\vcardGet($this->_to);
    }
    
    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }
    
    public function setMe()
    {
        $this->_me = true;
        return $this;
    }
    
    public function handle($stanza) {
        if($stanza->attributes()->from)
            $jid = current(explode('/',(string)$stanza->attributes()->from));
        else
            $jid = $this->_to;

        if($jid) {
            $evt = new \Event();
            
            $cd = new \modl\ContactDAO();
            
            $c = $cd->get($this->_to);
            
            if($c == null)
                $c = new \modl\Contact();

            $c->set($stanza, $this->_to);
            
            $cd->set($c);
            
            $c->createThumbnails();
            
            if(!$jid || $this->_to == $jid)
                $evt->runEvent('myvcard');
                
            $evt->runEvent('vcard', $c);
            $evt->runEvent('roster');
        }
    }
    
    public function errorItemNotFound($error) {
        $evt = new \Event();
        if($this->_me)
            $evt->runEvent('myvcardinvalid');
    }
}
