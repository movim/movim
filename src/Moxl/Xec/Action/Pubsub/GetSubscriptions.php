<?php
/*
 * GetSubscriptions.php
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
use Moxl\Stanza;

class GetSubscriptions extends Errors
{
    private $_to;
    private $_node;
    
    private $_sync = false;
    
    public function request() 
    {
        $this->store();
        Stanza\pubsubGetSubscriptions($this->_to, $this->_node);
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
    
    public function setSync()
    {
        $this->_sync = true;
        return $this;
    }
    
    public function handle($stanza) {
        $jid = current(explode('/',(string)$stanza->attributes()->to));
        $server = $this->_to;
        $node = $this->_node;
                
        $tab = array();
        foreach($stanza->pubsub->subscriptions->children() as $s) {
            $su = new \modl\Subscription();
            $su->set($jid, $server, $node, $s);
        
            $sd = new \modl\SubscriptionDAO();
            $sd->set($su);

            $sub = array(
                'jid' => (string)$s["jid"], 
                'subscription' => (string)$s["subscription"], 
                'subid' => (string)$s["subid"]);
            array_push($tab, $sub);
        }
        
        if(empty($tab)) {
            $sd = new \modl\SubscriptionDAO();
            $sd->deleteNode($server, $node);            
        }
        
        $evt = new \Event();
        $evt->runEvent(
            'pubsubsubscriptions', 
            array(
                'subscriptions' => $tab, 
                'to' => $this->_to, 
                'node' => $this->_node));
                
        if($this->_sync)
            $evt->runEvent('pubsubsubscribed', array($this->_to, $this->_node)); 
    }
}
