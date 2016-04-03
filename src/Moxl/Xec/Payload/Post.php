<?php
/*
 * @file Post.php
 *
 * @brief Handle incoming Post (XEP 0277 Microblog)
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

use Moxl\Xec\Action\Pubsub\GetItem;

class Post extends Payload
{
    private $testid = 'test_post';

    public function handle($stanza, $parent = false)
    {
        $from   = (string)$parent->attributes()->from;

        if($stanza->items->item
        && isset($stanza->items->item->entry)
        && (string)$stanza->items->item->entry->attributes()->xmlns == 'http://www.w3.org/2005/Atom') {
            if($parent->delay)
                $delay = gmdate('Y-m-d H:i:s', strtotime((string)$parent->delay->attributes()->stamp));
            else
                $delay = false;

            $p = new \modl\Postn();
            $p->set($stanza->items, $from, $delay);

            // We limit the very old posts (2 months old)
            if(strtotime($p->published) > mktime(0, 0, 0, gmdate("m")-2, gmdate("d"), gmdate("Y"))
            && $p->nodeid != $this->testid) {
                $pd = new \modl\PostnDAO();
                $pd->set($p, $from);

                $this->pack($p);
                $this->deliver();
            }
        } elseif($stanza->items->retract) {
            $pd = new \modl\PostnDAO();
            $pd->delete($stanza->items->retract->attributes()->id);

            $this->method('retract');

            $this->pack(array(
                    'server' => $from,
                    'node' => $stanza->attributes()->node
                ));
            $this->deliver();
        } elseif(isset($stanza->items->item->realtime)) {
            $this->method('ticker');

            $this->pack(array(
                    'server' => $from,
                    'node' => $stanza->items->attributes()->node,
                    'ticker' => $stanza->items->item->realtime
                ));
            $this->deliver();
        } elseif($stanza->items->item && isset($stanza->items->item->attributes()->id)
            && !filter_var($from, FILTER_VALIDATE_EMAIL)) {
            // In this case we only get the header, so we request the full content

            $p = new \modl\PostnDAO();

            $id = (string)$stanza->items->item->attributes()->id;
            $here = $p->exist($id);

            if(!$here && $id != $this->testid) {
                $d = new GetItem;
                $d->setTo($from)
                  ->setNode((string)$stanza->items->attributes()->node)
                  ->setId($id)
                  ->request();
            }
        }
    }
}
