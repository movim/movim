<?php
/*
 * GetAffiliations.php
 * 
 * Copyright 2012 nodpounod 
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
use Moxl\Xec\Action\Pubsub\Errors;

class GetAffiliations extends Errors
{
    private $_to;
    private $_node;
    
    public function request() 
    {
        $this->store();
        Pubsub::getAffiliations($this->_to, $this->_node);
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
    
    public function handle($stanza, $parent = false) {
        $evt = new \Event();
        
        $tab = array();
        foreach($stanza->pubsub->affiliations->children() as $i) {
            $sub = array((string)$i["jid"], (string)$i["affiliation"], (string)$i["subid"]);
            array_push($tab, $sub);
        }
        
        $evt->runEvent('pubsubaffiliations', array($tab, $this->_to,$this->_node)); 
    }
}
