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

namespace Moxl\Xec\Action\Notification;

use Moxl\Xec\Action;
use Moxl\Stanza\Notification;

class Get extends Action
{
    private $_to;
    
    public function request() 
    {
        $this->store();
        Notification::get($this->_to);
    }
    
    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }
    
    public function handle($stanza, $parent = false) {
        $evt = new \Event();

        \Cache::c('activenotifs', array());
        if($stanza->pubsub->items->item) {            
            foreach($stanza->pubsub->items->item as $item) {
                $evt->runEvent('notification', $item);
            }
            
            $evt->runEvent('notifications');
        } else {
            $evt->runEvent('nonotification');
        }
    }
    
    public function errorFeatureNotImplemented($stanza) {
        $evt = new \Event();
        $evt->runEvent('nonotification');
    }
    
    public function errorItemNotFound($stanza) {
        $evt = new \Event();
        $evt->runEvent('nonotification');
    }
    
    public function errorNotAuthorized($stanza) {
        $evt = new \Event();
        $evt->runEvent('nonotificationautorized');
    }
    
    public function errorNotAllowed($stanza) {
        $this->errorItemNotFound($stanza);
    }
    
}
