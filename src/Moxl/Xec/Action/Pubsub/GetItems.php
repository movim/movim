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
    private $_paging;
    private $_after;
    private $_before;

    private $_paginated = false;

    public function request()
    {
        $this->store();
        Pubsub::getItems($this->_to, $this->_node, $this->_paging, $this->_after, $this->_before);
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

    public function setPaging($paging)
    {
        $this->_paging = $paging;
        return $this;
    }

    public function setAfter($after)
    {
        $this->_after = $after;
        $this->_paginated = true;
        return $this;
    }

    public function setBefore($before = 'empty')
    {
        $this->_before = $before;
        $this->_paginated = true;
        return $this;
    }

    public function setSince($since)
    {
        $this->_since = $since;
        return $this;
    }

    public function handle($stanza, $parent = false)
    {
        $ids = [];

        foreach($stanza->pubsub->items->item as $item) {
            if (isset($item->entry)
            && (string)$item->entry->attributes()->xmlns == 'http://www.w3.org/2005/Atom') {
                if ($this->_since == null
                || strtotime($this->_since) < strtotime($item->entry->published)) {
                    $p = \App\Post::firstOrNew([
                        'server' => $this->_to,
                        'node' => $this->_node,
                        'nodeid' => (string)$item->attributes()->id
                    ]);
                    $p->set($item);
                    $p->save();

                    array_push($ids, $p->nodeid);
                }
            }
        }

        $first = $last = $count = null;

        if ($stanza->pubsub->set
        && $stanza->pubsub->set->attributes()->xmlns == 'http://jabber.org/protocol/rsm') {
            $first = (string)$stanza->pubsub->set->first;
            $last = (string)$stanza->pubsub->set->last;
            $count = (string)$stanza->pubsub->set->count;
        }

        $this->pack([
            'server'    => $this->_to,
            'node'      => $this->_node,
            'ids'       => $ids,
            'first'     => $first,
            'last'      => $last,
            'count'     => $count,
            'paginated' => $this->_paginated
        ]);

        $this->deliver();
    }

    public function error($errorid, $message)
    {
        $this->pack(['server' => $this->_to, 'node' => $this->_node]);
        $this->deliver();
    }

}
