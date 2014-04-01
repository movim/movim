<?php
/*
 * GetMetadata.php
 * 
 * Copyright 2013 edhelas
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

namespace Moxl\Xec\Action\Pubsub;

use Moxl\Xec\Action;
use Moxl\Stanza\Pubsub;

class GetMetadata extends Errors
{
    private $_to;
    private $_node;
    
    public function request() 
    {
        $this->store();
        Pubsub::getMetadata($this->_to, $this->_node);
    }
    
    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }
    
    public function setNode($node)
    {
        $this->_node = $node;
        return $this;
    }
    
    public function handle($stanza) {
        $evt = new \Event();
        
        $tab = array();

        \movim_log($stanza);
        foreach($stanza->query->x->children() as $i) {
            $explode = explode('#', (string)$i->attributes()->var);
            $key = end($explode);
            $tab[$key] = (string)$i->value;
        }

        unset($tab['FORM_TYPE']);

        $evt->runEvent('pubsubmetadata', array($tab, $this->_to,$this->_node)); 
    }
}
