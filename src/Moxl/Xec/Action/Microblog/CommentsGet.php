<?php
/*
 * CommentsGet.php
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

namespace Moxl\Xec\Action\Microblog;

use Moxl\Xec\Action;
use Moxl\Stanza;

class CommentsGet extends Action
{
    private $_to;
    private $_id;
    
    public function request() 
    {
        $this->store();
        Stanza\microblogCommentsGet($this->_to, $this->_id);
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
    
    public function handle($stanza) {
        $evt = new \Event();
        
        $to = current(explode('/',(string)$stanza->attributes()->to));

        $node = (string)$stanza->pubsub->items->attributes()->node;
        list($xmlns, $parent) = explode("/", $node);

        if($stanza->pubsub->items->item) {
            
            $comments = array();

            foreach($stanza->pubsub->items->item as $item) {
                $p = new \modl\Postn();
                $p->set($item, $this->_to, false, $node);
                
                $pd = new \modl\PostnDAO();
                $pd->set($p);
                
                array_unshift($comments, $p);
            }
            
            $evt->runEvent('comment', $parent);
        } else {
            $evt->runEvent('nocomment', $parent);   
        }
    }
    
    public function errorFeatureNotImplemented() {
        $evt = new \Event();
        $evt->runEvent('nocommentstream', $this->_id);
    }
    
    public function errorItemNotFound() {        
        $evt = new \Event();
        $evt->runEvent('nocommentstream', $this->_id);
    }
    
    public function errorRemoteServerNotFound($stanza) {
        $this->errorItemNotFound($stanza);
    }
    
    public function errorNotAuthorized() {
        $evt = new \Event();
        $evt->runEvent('nostreamautorized', $this->_id);
    }

}
