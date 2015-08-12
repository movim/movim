<?php
/*
 * Action.php
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

namespace Moxl\Xec;

use Moxl\Utils;
use Moxl\Xec\Payload\Payload;

abstract class Action extends Payload
{    
    final public function store() 
    {
        $sess = \Session::start();
        //$_instances = $sess->get('xecinstances');
        
        // Set a new Id for the Iq request
        $session = \Sessionx::start();

        // Generating the iq key.
        $id = $session->id = \generateKey(6);
        
        // We serialize the current object
        $obj = new \StdClass;
        $obj->type   = get_class($this);
        $obj->object = serialize($this);
        $obj->time   = time();
        
        //$_instances = $this->clean($_instances);
        
        $sess->set($id, $obj);
    }
    
    /*
     * Clean old IQ requests
     */
    private function clean($instances)
    {
        $t = time();
        foreach($instances as $key => $i) {
            if($i['time'] < (int)$t-30) {
                Utils::log('Action : Clean this request after 30 sec of no feedback '.$i['type']);
                unset($instances[$key]);
            }
        }
        
        return $instances;
    }
    
    abstract public function request();
}
