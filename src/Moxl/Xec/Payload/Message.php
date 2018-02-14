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

        if ($stanza->confirm
        && $stanza->confirm->attributes()->xmlns == 'http://jabber.org/protocol/http-auth') {
            return;
        }

        if ($stanza->composing)
            $this->event('composing', [$jid[0], $to]);
        if ($stanza->paused)
            $this->event('paused', [$jid[0], $to]);
        if ($stanza->gone)
            $this->event('gone', [$jid[0], $to]);

        $m = new \Modl\Message;
        $m->set($stanza, $parent);

        $md = new \Modl\MessageDAO;
        $md->set($m);

        if ($m->body || $m->subject) {
            $this->pack($m);
            $this->deliver();
        }
    }
}
