<?php
/*
 * @file Message.php
 * 
 * @brief Handle incoming messages
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

class Message extends Payload
{
    public function handle($stanza, $parent = false) {        
        $jid = explode('/',(string)$stanza->attributes()->from);
        $to = current(explode('/',(string)$stanza->attributes()->to));

        $evt = new \Event();

        if($stanza->composing)
            $evt->runEvent('composing', array($jid[0], $to));
        if($stanza->paused)
            $evt->runEvent('paused', array($jid[0], $to));
        if($stanza->gone)
            $evt->runEvent('gone', array($jid[0], $to));
        if($stanza->body || $stanza->subject) {
            $m = new \modl\Message();

            $m->session     = $to;
            $m->jidto      = $to;
            $m->jidfrom    = $jid[0];

            if(isset($jid[1]))
                $m->ressource = $jid[1];
            
            $m->type    = (string)$stanza->attributes()->type;
            
            $m->body    = (string)$stanza->body;
            $m->subject = (string)$stanza->subject;

            if($stanza->html) {
                $m->html = \cleanHTMLTags($stanza->html->body->asXML());
                $m->html = \fixSelfClosing($m->html);
            }
            
            if($stanza->delay)
                $m->published = date('Y-m-d H:i:s', strtotime($stanza->delay->attributes()->stamp));
            else
                $m->published = date('Y-m-d H:i:s');
            $m->delivered = date('Y-m-d H:i:s');
            
            $md = new \modl\MessageDAO();
            $md->set($m);
                    
            $this->pack($m);
            $this->deliver();
        }
    }
}
