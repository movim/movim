<?php
/*
 * @file Jingle.php
 *
 * @brief Handle Jingle stanza
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

use Moxl\Stanza\Ack;
use Moxl\Stanza\Jingle as JingleStanza;
use Movim\Session;

class Jingle extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $from = (string)$parent->attributes()->from;
        $id   = (string)$parent->attributes()->id;

        $action = (string)$stanza->attributes()->action;

        $userid = \App\User::me()->id;
        $message = new \App\Message;
        $message->user_id = $userid;
        $message->id = 'm_' . generateUUID();
        $message->jidto = $userid;
        $message->jidfrom = current(explode('/', (string)$from));
        $message->published = gmdate('Y-m-d H:i:s');
        $message->thread = (string)$stanza->attributes()->sid;

        $sid = Session::start()->get('jingleSid');

        if ($sid == $message->thread) {
            Ack::send($from, $id);

            switch ($action) {
                case 'session-initiate':
                    $message->type = 'jingle_start';
                    $message->save();
                    $this->event('jingle_sessioninitiate', [$stanza, $from]);
                    break;
                case 'session-info':
                    if ($stanza->mute) {
                        $this->event('jingle_sessionmute', 'mid'.(string)$stanza->mute->attributes()->name);
                    }
                    if ($stanza->unmute) {
                        $this->event('jingle_sessionunmute', 'mid'.(string)$stanza->unmute->attributes()->name);
                    }
                    break;
                case 'transport-info':
                    $this->event('jingle_transportinfo', $stanza);
                    break;
                case 'session-terminate':
                    $message->type = 'jingle_end';
                    $message->save();
                    $this->event('jingle_sessionterminate', (string)$stanza->reason->children()[0]->getName());
                    break;
                case 'session-accept':
                    $message->type = 'jingle_start';
                    $message->save();
                    $this->event('jingle_sessionaccept', $stanza);
                    break;
            }
        } else {
            JingleStanza::unknownSession($from, $id);
        }
    }
}
