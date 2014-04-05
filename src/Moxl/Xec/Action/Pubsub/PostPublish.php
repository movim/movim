<?php
/*
 * PostPublish.php
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
use Moxl\Stanza\PubsubAtom;
use Moxl\Action\Microblog\CommentCreateNode;
use Moxl\Xec\Action\Pubsub\Errors;

class PostPublish extends Errors
{
    private $_node;
    private $_to = '';
    private $_atom;
    
    public function __construct() {
        $this->_atom = new PubsubAtom;
    }
    
    public function request() 
    {
        if($this->_to == '')
            $this->_to = $this->_atom->jid;

        $this->store();

        Pubsub::postPublish($this->_to, $this->_node, $this->_atom);
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
    
    public function setFrom($from)
    {
        $this->_atom->jid = $from;
        return $this;
    }
    
    public function setTitle($title)
    {
        $this->_atom->title = $title;
        return $this;
    }
    
    public function setContent($content)
    {
        $this->_atom->content = $content;
        return $this;
    }
    
    public function setContentHtml($content)
    {
        $this->_atom->contenthtml = $content;
        return $this;
    }
    
    public function setLocation($geo)
    {
        $this->_atom->geo = $geo;
        return $this;
    }
    
    public function setName($name)
    {
        $this->_atom->name = $name;
        return $this;
    }

    public function enableComments()
    {
        $this->_atom->enableComments();
        return $this;
    }
    
    public function handle($stanza) {       
        $evt = new \Event();
        
        $p = new \modl\Postn();

        $p->key     = $this->_atom->jid;
        $p->from    = $this->_to;
        
        $p->node    = $this->_node;
        $p->nodeid  = $this->_atom->id;
        
        $p->aname   = $this->_atom->name;
        $p->aid     = $this->_atom->jid;

        if(isset($this->_atom->content))
            $p->content = $this->_atom->content;
        elseif(isset($this->_atom->contenthtml))
            $p->content = $this->_atom->contenthtml;
        
        $p->published = date('Y-m-d H:i:s');
        $p->updated = date('Y-m-d H:i:s');
        
        $pd = new \modl\PostnDAO();
        $pd->set($p);

        if($this->_atom->comments) {
            $mc = new CommentCreateNode;
            $mc->setTo($this->_to)
               ->setParentId($p->nodeid)
               ->request();
        }
        
        $evt->runEvent('stream', array('from' => $p->from, 'node' => $p->node));
    }
}
