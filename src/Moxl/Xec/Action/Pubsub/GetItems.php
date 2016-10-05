<?php
/*
 * GetItems.php
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

namespace Moxl\Xec\Action\Pubsub;

use Moxl\Xec\Action;
use Moxl\Stanza\Pubsub;
use Moxl\Xec\Action\Pubsub\Errors;

class GetItems extends Errors
{
    private $_to;
    private $_node;
    private $_since;

    public function request()
    {
        $this->store();
        Pubsub::getItems($this->_to, $this->_node);
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

    public function setSince($since)
    {
        $this->_since = $since;
        return $this;
    }

    public function handle($stanza, $parent = false) {
        $pd = new \modl\PostnDAO();

        foreach($stanza->pubsub->items->item as $item) {
            if(isset($item->entry)
            && (string)$item->entry->attributes()->xmlns == 'http://www.w3.org/2005/Atom') {
                if($this->_since == null
                || strtotime($this->_since) < strtotime($item->entry->published)) {
                    $p = new \modl\Postn();
                    $promise = $p->set($item, $this->_to, false, $this->_node);

                    $promise->done(function() use($pd, $p) {
                        $pd->set($p);

                        $this->pack(['server' => $this->_to, 'node' => $this->_node]);
                        $this->deliver();
                    });
                }
            }
        }
    }

    public function error($errorid, $message) {
        $this->pack(['server' => $this->_to, 'node' => $this->_node]);
        $this->deliver();
    }

}
