<?php
/*
 * PostPublish.php
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

namespace Moxl\Xec\Action\Pubsub;

use Moxl\Xec\Action;
use Moxl\Stanza\Pubsub;
use Moxl\Stanza\PubsubAtom;
use Moxl\Xec\Action\Pubsub\GetItem;
use Moxl\Xec\Action\Pubsub\Errors;

class PostPublish extends Errors
{
    private $_node;
    private $_to = '';
    private $_atom;
    private $_repost;

    public function __construct() {
        parent::__construct();
        $this->_atom = new PubsubAtom;
    }

    public function request()
    {
        if($this->_to == '')
            $this->_to = $this->_atom->jid;

        $this->store();

        Pubsub::postPublish($this->_to, $this->_node, $this->_atom);
    }

    public function setTo($to)
    {
        $this->_to = $to;
        $this->_atom->to = $to;
        return $this;
    }

    public function setId($id)
    {
        $this->_atom->id = $id;
        return $this;
    }

    public function setNode($node)
    {
        $this->_node = $node;
        $this->_atom->node = $node;
        return $this;
    }

    public function setFrom($from)
    {
        $this->_atom->jid = $from;
        return $this;
    }

    public function setTitle($title)
    {
        $this->_atom->title = $title;
        return $this;
    }

    public function setLink(
        $href,
        $title = null,
        $type = 'text/html',
        $description = null,
        $logo = null)
    {
        $this->_atom->link = [
            'href'  => $href,
            'title' => $title,
            'type'  => $type,
            'description' => $description,
            'logo'  => $logo
        ];
        return $this;
    }

    public function setRepost($repost)
    {
        $this->_atom->repost = $repost;
        $this->_repost = true;
        return $this;
    }

    public function setReply($ref)
    {
        $this->_atom->reply = $ref;
        return $this;
    }

    public function setPublished($published)
    {
        $this->_atom->published = $published;
        return $this;
    }

    public function setImage($href, $title = null, $type = null)
    {
        $this->_atom->image = [
            'href' => $href,
            'title' => $title,
            'type' => $type
        ];

        return $this;
    }

    public function setContent($content)
    {
        $this->_atom->content = $content;
        return $this;
    }

    public function setContentXhtml($content)
    {
        $this->_atom->contentxhtml = $content;
        return $this;
    }

    public function setLocation($geo)
    {
        $this->_atom->geo = $geo;
        return $this;
    }

    public function setName($name)
    {
        $this->_atom->name = $name;
        return $this;
    }

    public function setTags($tags)
    {
        $this->_atom->tags = $tags;
        return $this;
    }

    public function enableComments($server = true)
    {
        $this->_atom->enableComments($server);
        return $this;
    }

    public function isOpen()
    {
        $this->_atom->isOpen();
        return $this;
    }

    public function handle($stanza, $parent = false)
    {
        $g = new GetItem;
        $g->setTo($this->_to)
          ->setNode($this->_node)
          ->setId($this->_atom->id)
          ->request();

        $this->pack([
            'to'        => $this->_to,
            'node'      => $this->_node,
            'id'        => $this->_atom->id,
            'repost'    => $this->_repost,
            'comments'  => $this->_atom->comments]);
        $this->deliver();
    }
}
