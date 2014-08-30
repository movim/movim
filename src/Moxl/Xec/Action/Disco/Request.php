<?php
/*
 * Request.php
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

namespace Moxl\Xec\Action\Disco;

use Moxl\Xec\Action;
use Moxl\Stanza\Disco;

class Request extends Action
{
    private $_node;
    private $_to;
    
    public function request() 
    {
        $this->store();
        Disco::request($this->_to, $this->_node);
    }
    
    public function setNode($node)
    {
        $this->_node = $node;
        return $this;
    }
    
    public function setTo($to)
    {
        $this->_to = echapJid($to);
        return $this;
    }
    
    public function handle($stanza, $parent = false) {
        $c = new \modl\Caps();

        if(isset($this->_node))
            $c->set($stanza, $this->_node);
        else
            $c->set($stanza, $this->_to);
        
        if(
            $c->node != ''
         && $c->category != ''
         && $c->type != ''
         && $c->name != '') {
            $cd = new \modl\CapsDAO();
            $cd->set($c);
        }
    }
}
