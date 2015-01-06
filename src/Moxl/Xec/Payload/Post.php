<?php
/*
 * @file Post.php
 * 
 * @brief Handle incoming Post (XEP 0277 Microblog)
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

use Moxl\Xec\Action\Pubsub\GetItem;

class Post extends Payload
{
    public function handle($stanza, $parent = false) {  
        $from   = (string)$parent->attributes()->from;
        
        if($stanza->item && isset($stanza->item->entry)) {
            if($parent->delay)
                $delay = date('Y-m-d H:i:s', strtotime((string)$parent->delay->attributes()->stamp));
            else
                $delay = false;
            
            $p = new \modl\Postn();
            $p->set($stanza, $from, $delay);
            
            // We limit the very old posts (2 months old)
            if(strtotime($p->published) > mktime(0, 0, 0, date("m")-2, date("d"), date("Y"))) {
                $pd = new \modl\PostnDAO();
                $pd->set($p);

                $this->pack($p);
                $this->deliver();
                
                //$evt->runEvent('post', array('from' => $from, 'node' => $p->node));
            }
        } elseif($stanza->retract) {
            $pd = new \modl\PostnDAO();
            $pd->delete($stanza->retract->attributes()->id);
                
            $evt = new \Event();
            $evt->runEvent(
                'post', 
                array(
                    'from' => $from, 
                    'node' => $stanza->attributes()->node
                )
            );
        } elseif($stanza->item && isset($stanza->item->attributes()->id)
            && !filter_var($from, FILTER_VALIDATE_EMAIL)) {
            // In this case we only get the header, so we request the full content
                
            $p = new \modl\PostnDAO();

            $id = (string)$stanza->item->attributes()->id;
            $here = $p->exist($id);

            if(!$here) {
                $d = new GetItem;
                $d->setTo($from)
                  ->setNode((string)$stanza->attributes()->node)
                  ->setId($id)
                  ->request();
            }
        }
    }
}
