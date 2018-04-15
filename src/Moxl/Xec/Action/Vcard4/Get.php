<?php
/*
 * Get.php
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
namespace Moxl\Xec\Action\Vcard4;

use Moxl\Xec\Action;
use Moxl\Stanza\Vcard4;
use App\Contact;

class Get extends Action
{
    private $_to;
    private $_me = false;

    public function request()
    {
        $this->store();
        Vcard4::get($this->_to);
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

    public function handle($stanza, $parent = false)
    {
        if($vcard = $stanza->pubsub->items->item) {
            $contact = \App\Contact::firstOrNew(['id' => $this->_to]);
            $contact->setVcard4($stanza->pubsub->items->item->vcard);
            $contact->save();

            $this->pack($contact);
            $this->deliver();
        } else {
            $this->error(false);
        }
    }

    public function error($error)
    {
        $r = new \Moxl\Xec\Action\Vcard\Get;
        $r->setTo($this->_to)->request();
    }
}
