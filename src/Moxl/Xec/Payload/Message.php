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
    public function handle($stanza, $parent = false)
    {
        $jid = explode('/',(string)$stanza->attributes()->from);
        $to = current(explode('/',(string)$stanza->attributes()->to));

        if($stanza->composing)
            $this->event('composing', array($jid[0], $to));
        if($stanza->paused)
            $this->event('paused', array($jid[0], $to));
        if($stanza->gone)
            $this->event('gone', array($jid[0], $to));
        if($stanza->body || $stanza->subject) {
            if($stanza->request) {
                $from = (string)$stanza->attributes()->from;
                $id = (string)$stanza->attributes()->id;
                \Moxl\Stanza\Message::receipt($from, $id);
            }

            $m = new \Modl\Message;
            $m->set($stanza, $parent);

            if(!preg_match('#^\?OTR#', $m->body)) {
                $md = new \Modl\MessageDAO;
                $md->set($m);

                $this->pack($m);
                $this->deliver();
            }
        }
    }
}
