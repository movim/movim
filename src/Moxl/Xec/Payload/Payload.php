<?php
/*
 * @file Action.php
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

namespace Moxl\Xec\Payload;

use Moxl\Xec\Payload\Packet;
use Moxl\Utils;

abstract class Payload
{
    protected $method;
    protected $packet;
    /**
     * Constructor of class Payload.
     *
     * @return void
     */
    public function __construct()
    {
        $this->packet = new Packet;
    }

    /**
     * Prepare the packet
     *
     * @return void
     */
    final public function prepare($stanza, $parent = false)
    {
        if($parent === false) {
            $this->packet->from = current(explode('/',(string)$stanza->attributes()->from));
        } else {
            $this->packet->from = current(explode('/',(string)$parent->attributes()->from));
        }
    }

    /**
     * Deliver the packet
     *
     * @return void
     */
    final public function deliver() {
        $action_ns = 'Moxl\Xec\Action';
        if(get_parent_class($this) == $action_ns) {
            $class = str_replace(array($action_ns, '\\'), array('', '_'), get_class($this));
            $key = strtolower(substr($class, 1));
        } else {
            $class = strtolower(get_class($this));
            $pos = strrpos($class, '\\');
            $key = substr($class, $pos + 1);
        }
        if($this->method)
            $key = $key . '_' . $this->method;

        Utils::log('Package : Event "'.$key.'" from "'.$this->packet->from.'" fired');

        $evt = new \Event();
        $evt->runEvent($key, $this->packet);
    }

    /**
     * Set a specific method for the packet to specialize the key
     *
     * @return void
     */
    final public function method($method) {
        $this->method = strtolower($method);
    }

    /**
     * Set the content of the packet
     *
     * @return void
     */
    final public function pack($content) {
        $this->packet->content = $content;
    }
    
    abstract public function handle($stanza, $parent = false);
}
