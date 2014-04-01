<?php
/*
 * Publish.php
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

namespace Moxl\Xec\Action\Message;

use Moxl\Xec\Action;
use Moxl\Stanza;

class Publish extends Action
{
    private $_to;
    private $_content;
    private $_muc = false;
    private $_encrypted = false;
    
    public function request() 
    {
        $this->store();
        if($this->_muc)
            Stanza\messageMuc($this->_to, $this->_content);
        elseif($this->_encrypted)
            Stanza\messageEncrypted($this->_to, $this->_content);
        else
            Stanza\message($this->_to, $this->_content);
    }
    
    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }
    
    public function setMuc()
    {
        $this->_muc = true;
        return $this;
    }

    public function setEncrypted($bool)
    {
        $this->_encrypted = $bool;
        return $this;
    }
    
    public function setContent($content)
    {
        $this->_content = $content;
        return $this;
    }
    
    public function handle($stanza) {     
        $evt = new \Event();
        $evt->runEvent('messagepublished', $this->_to);
    }
}
