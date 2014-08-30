<?php
/*
 * CommentPublish.php
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
use Moxl\Stanza\Pubsub;
use Moxl\Stanza\PubsubAtom;
use Moxl\Xec\Action\Pubsub\Errors;

class CommentPublish extends Errors
{
    private $_to;
    private $_node;
    private $_parentid;

    private $_atom;
    
    public function __construct() {
        $this->_atom = new PubsubAtom;
    }
    
    public function request() 
    {       
        $this->store();   
        Pubsub::postPublish($this->_to, $this->_node, $this->_atom);
    }
    
    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }

    public function setParentid($parentid)
    {
        $this->_parentid = $parentid;
        $this->_node = 'urn:xmpp:microblog:0:comments/'.$this->_parentid;
        return $this;
    }

    public function setContent($content)
    {
        $this->_atom->content = $content;
        return $this;
    }
    
    public function setName($name)
    {
        $this->_atom->name = $name;
        return $this;
    }
    
    public function setFrom($from)
    {
        $this->_atom->jid = $from;
        return $this;
    }
        
    public function handle($stanza, $parent = false) {
        $evt = new \Event();
        
        $p = new \modl\Postn();

        $p->session = $this->_atom->jid;
        $p->jid     = $this->_atom->jid;
        
        $p->node    = $this->_node;
        $p->nodeid  = $this->_atom->id;
        
        $p->aname   = $this->_atom->name;
        $p->aid     = $this->_atom->jid;
        
        $p->content = $this->_atom->content;
        
        $p->published = date('Y-m-d H:i:s');
        $p->updated = date('Y-m-d H:i:s');
        
        $pd = new \modl\PostnDAO();
        $pd->set($p);
        
        $evt->runEvent('comment', $this->_parentid);
    }
    
    public function errorFeatureNotImplemented($stanza) {
        $evt = new \Event();
        $evt->runEvent('commentpublisherror');
    }
    
    public function errorNotAuthorized($stanza) {
        $evt = new \Event();
        $evt->runEvent('commentpublisherror');
    }
    
    public function errorServiceUnavailable($stanza) {
        $evt = new \Event();
        $evt->runEvent('commentpublisherror');  
    }
    
    public function errorItemNotFound($stanza) {
        $evt = new \Event();
        $evt->runEvent('commentpublisherror');          
    }

}
