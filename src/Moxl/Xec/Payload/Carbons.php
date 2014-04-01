<?php
/*
 * @file Carbons.php
 * 
 * @brief Handle incoming carbons messages
 * 
 * Copyright 2014 edhelas <edhelas@edhelas-laptop>
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

class Carbons extends Payload
{
    public function handle($stanza, $parent = false) {
        $stanza = $stanza->forwarded->message;

        $jid = explode('/',(string)$stanza->attributes()->from);
        $to = current(explode('/',(string)$stanza->attributes()->to));

        $evt = new \Event();

        

        if($stanza->composing) {
            if($parent->attributes()->from == $jid[0])
                $evt->runEvent('composing', $to);
            else
                $evt->runEvent('composing', $jid[0]);
        }
        
        if($stanza->paused) {
            if($parent->attributes()->from == $jid[0])
                $evt->runEvent('paused', $to);
            else
                $evt->runEvent('paused', $jid[0]);
        }
        
        if($stanza->gone) {
            if($parent->attributes()->from == $jid[0])
                $evt->runEvent('gone', $to);
            else
                $evt->runEvent('gone', $jid[0]);
        }
        
        if($stanza->body || $stanza->subject) {
            $m = new \modl\Message();

            $m->session     = $parent->attributes()->from;
            $m->jidto      = $to;
            $m->jidfrom    = $jid[0];
            
            $m->ressource = $jid[1];
            
            $m->type    = (string)$stanza->attributes()->type;
            
            $m->body    = (string)$stanza->body;
            $m->subject = (string)$stanza->subject;
            
            if($stanza->delay)
                $m->published = date('Y-m-d H:i:s', strtotime($stanza->delay->attributes()->stamp));
            else
                $m->published = date('Y-m-d H:i:s');
            $m->delivered = date('Y-m-d H:i:s');
            
            $md = new \modl\MessageDAO();
            $md->set($m);
                    
            $evt->runEvent('message', $m);
        }
    }
}
