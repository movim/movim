<?php
/*
 * Get.php
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

namespace Moxl\Xec\Action\Vcard;

use Moxl\Xec\Action;
use Moxl\Stanza\Vcard;

class Get extends Action
{
    private $_to;
    private $_me = false;
    private $_muc = false;

    public function request()
    {
        $this->store();
        Vcard::get($this->_to);
    }

    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }

    public function setMe()
    {
        $this->_me = true;
        return $this;
    }

    public function isMuc()
    {
        $this->_muc = true;
        return $this;
    }

    public function handle($stanza, $parent = false)
    {
        if($stanza->attributes()->from) {
            $jid = current(explode('/',(string)$stanza->attributes()->from));
        } else {
            $jid = $this->_to;
        }

        if($this->_muc) {
            $c = new \Modl\Conference;
            $c->setAvatar($stanza, $this->_to);
        } elseif($jid) {
            $cd = new \Modl\ContactDAO;

            $c = $cd->get($this->_to);

            if($c == null)
                $c = new \Modl\Contact;

            $c->set($stanza, $this->_to);

            $cd->set($c);

            $c->createThumbnails();

            $this->pack($c);
            $this->deliver();
        }
    }
}
