<?php
/*
 * DiscoItems.php
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

namespace Moxl\Xec\Action\Pubsub;

use Moxl\Xec\Action;
use Moxl\Stanza\Disco;
use Moxl\Xec\Action\Pubsub\Errors;

class DiscoItems extends Errors
{
    private $_to;
    
    public function request() 
    {
        $this->store();
        Disco::items($this->_to);
    }
    
    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }
    
    public function handle($stanza, $parent = false) {
        $evt = new \Event();
        
        $nd = new \modl\ItemDAO();
        
        foreach($stanza->query->item as $item) {
            $n = new \modl\Item();
            $n->set($item, $this->_to);
            if(substr($n->node, 0, 29) != 'urn:xmpp:microblog:0:comments')
                $nd->set($n);
        }

        $evt->runEvent('discoitems', $this->_to);
    }

    public function error($error) {
        $evt = new \Event();
        $evt->runEvent('discoerror', $server);
    }
}
