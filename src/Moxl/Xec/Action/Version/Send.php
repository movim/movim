<?php
/*
 * Send.php
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

namespace Moxl\Xec\Action\Version;

use Moxl\Xec\Action;
use Moxl\Stanza\Version;

class Send extends Action
{
    private $_to;
    private $_id;
    private $_name;
    private $_version;
    private $_os;
    
    public function request() 
    {
        //$this->store();
        Version::send($this->_to, $this->_id, $this->_name, $this->_version, $this->_os);
    }
    
    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }
    
    public function setId($id)
    {
        $this->_id = $id;
        return $this;
    }
    
    public function setName($name)
    {
        $this->_name = $name;
        return $this;
    }
    
    public function setVersion($version)
    {
        $this->_version = $version;
        return $this;
    }
    
    public function setOs($os)
    {
        $this->_os = $os;
        return $this;
    }
    
    public function handle($stanza, $parent = false) {

    }
}
