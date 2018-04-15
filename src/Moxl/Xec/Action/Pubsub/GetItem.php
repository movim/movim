<?php
/*
 * GetItem.php
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

namespace Moxl\Xec\Action\Pubsub;

use Moxl\Xec\Action;
use Moxl\Stanza\Pubsub;
use Moxl\Xec\Action\Pubsub\Errors;

class GetItem extends Errors
{
    private $_to;
    private $_node;
    private $_id;
    private $_askreply;

    private $_parentid;

    public function request()
    {
        $this->store();
        Pubsub::getItem($this->_to, $this->_node, $this->_id);
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

    public function setId($id)
    {
        $this->_id = $id;
        return $this;
    }

    public function setAskReply($reply)
    {
        $this->_askreply = $reply;
        return $this;
    }

    public function setParentId($parentid)
    {
        $this->_parentid = $parentid;
        return $this;
    }

    public function handle($stanza, $parent = false)
    {
        if ($stanza->pubsub->items->item) {
            foreach($stanza->pubsub->items->item as $item) {
                if (isset($item->entry)
                &&(string)$item->entry->attributes()->xmlns == 'http://www.w3.org/2005/Atom') {
                    $p = \App\Post::firstOrNew([
                        'server' => $this->_to,
                        'node' => $this->_node,
                        'nodeid' => $this->_id
                    ]);
                    $p->set($item, $this->_to, false, $this->_node);

                    if (isset($this->_parentid)) {
                        $p->parent_id    = $this->_parentid;
                    }

                    if ($p->isComment() && !isset($this->_parentid)) return;

                    $p->save();

                    if (is_array($this->_askreply)) {
                        $this->pack(\App\Post::find($this->_askreply));
                        $this->deliver();
                    } else {
                        $this->pack($p);
                        $this->event('post', $this->packet);
                    }

                    $this->pack($p);
                    $this->deliver();
                }
            }
        } else {
            $pd = new PostDelete;
            $pd->setTo($this->_to)
               ->setNode($this->_node)
               ->setId($this->_id);

            $pd->handle($stanza);
        }
    }

    public function errorItemNotFound($stanza, $parent = false)
    {
        $this->errorServiceUnavailable($stanza, $parent);
    }

    public function errorServiceUnavailable($stanza, $parent = false)
    {
        $pd = new PostDelete;
        $pd->setTo($this->_to)
           ->setNode($this->_node)
           ->setId($this->_id);

        $pd->handle($stanza);
    }
}
