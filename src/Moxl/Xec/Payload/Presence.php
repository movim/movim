<?php
/*
 * @file Presence.php
 *
 * @brief Handle incoming presences
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

use Moxl\Xec\Action\Vcard\Get;

class Presence extends Payload
{
    public function handle($stanza, $parent = false)
    {
        // Subscribe request
        if((string)$stanza->attributes()->type == 'subscribe') {
            $session = \Session::start();
            $notifs = $session->get('activenotifs');
            $notifs[(string)$stanza->attributes()->from] = 'sub';
            $session->set('activenotifs', $notifs);

            $this->event('subscribe', (string)$stanza->attributes()->from);
        } else {
            $p = new \Modl\Presence;
            $p->setPresence($stanza);

            $pd = new \Modl\PresenceDAO;
            $pd->set($p);

            /*if($p->photo) {
                $r = new Get;
                $r->setTo(echapJid((string)$stanza->attributes()->from))->request();
            }*/

            if($p->muc
            && isset($stanza->x)
            && isset($stanza->x->status)) {
                $code = (string)$stanza->x->status->attributes()->code;
                if(isset($code) && $code == '110') {
                    if($p->value != 5 && $p->value != 6) {
                        $this->method('muc_handle');
                        $this->pack($p);
                    } elseif($p->value == 5) {
                        $pd->clearMuc($p->jid);

                        $this->method('unavailable_handle');
                        $this->pack($p);
                        $this->deliver();
                    }
                }
            } else {
                $cd = new \Modl\ContactDAO();
                $c = $cd->getRosterItem($p->jid, true);

                $this->pack($c);

                if($p->value == 5 /*|| $p->value == 6*/) {
                    $pd->delete($p);
                }
            }

            $this->deliver();
        }
    }
}
