<?php
/*
 * Subscribe.php
 * 
 * Copyright 2013 edhelas <edhelas@edhelas-laptop>
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

class Subscribe extends Errors
{
    private $_to;
    private $_from;
    private $_node;
    private $_data;
    
    public function request() 
    {
        $this->store();
        Pubsub::subscribe($this->_to, $this->_from, $this->_node);
    }
    
    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }
    
    public function setFrom($from)
    {
        $this->_from = $from;
        return $this;
    }
    
    public function setNode($node)
    {
        $this->_node = $node;
        return $this;
    }
    
    public function setData($data)
    {
        $this->_data = $data;
        return $this;
    }
    
    public function handle($stanza) {
        $jid = $this->_from;
        $server = $this->_to;
        $node = $this->_node;
        
        $s = $stanza->pubsub->subscription;
        
        $su = new \modl\Subscription();
        $su->set($jid, $server, $node, $s);
    
        $sd = new \modl\SubscriptionDAO();
        $sd->set($su);

        //add the group to the public list (if checked)
        if($this->_data['listgroup'] == true){
            $add = new \moxl\PubsubSubscriptionListAdd();
            $add->setTo($this->_to)
              ->setNode($this->_node)
              ->setFrom($this->_from)
              ->setData($this->_data)
              ->request();
        }
        
        $evt = new \Event();
        $evt->runEvent('pubsubsubscribed', array($this->_to, $this->_node)); 
    }
    
    public function error($stanza) {
        parent::error($stanza);
        $evt = new \Event();
        $evt->runEvent('pubsubsubscribederror', array($this->_to, $this->_node)); 
    }
    
}
