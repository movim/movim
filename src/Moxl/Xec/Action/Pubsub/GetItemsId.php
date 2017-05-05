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
use Moxl\Stanza\Disco;
use Moxl\Xec\Action\Pubsub\Errors;
use Moxl\Xec\Action\Pubsub\GetItem;

use Movim\Widget\Event;

class GetItemsId extends Errors
{
    private $_to;
    private $_node;

    public function request()
    {
        $this->store();
        Disco::items($this->_to, $this->_node);
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

    public function handle($stanza, $parent = false)
    {
        $evt = new Event;

        $pd = new \Modl\PostnDAO;

        $ids = [];

        foreach(array_reverse($stanza->query->xpath('item')) as $item) {
            $id = (string)$item->attributes()->name;
            if(!$pd->exists($this->_to, $this->_node, $id)
            && !empty($id)) {
                $gi = new GetItem;
                $gi->setTo($this->_to)
                   ->setNode($this->_node)
                   ->setId($id)
                   ->request();
            }

            array_push($ids, $id);
        }

        $this->pack(['server' => $this->_to, 'node' => $this->_node, 'ids' => $ids]);
        $this->deliver();
    }

    public function error($errorid, $message)
    {
        $this->pack(['server' => $this->_to, 'node' => $this->_node]);
        $this->deliver();
    }

}
