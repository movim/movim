<?php

namespace Moxl\Xec\Action\Pubsub;

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

    public function __construct()
    {
        parent::__construct();
        $this->_atom = new PubsubAtom;
    }

    public function request()
    {
        if ($this->_to == '') {
            $this->_to = $this->_atom->jid;
        }

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

    public function addLink(
        $href,
        $title = null,
        $type = 'text/html',
        $description = null,
        $logo = null
    ) {
        array_push($this->_atom->links, [
            'href'  => $href,
            'title' => $title,
            'type'  => $type,
            'description' => $description,
            'logo'  => $logo
        ]);

        return $this;
    }

    public function addImage($href, $title = null, $type = null)
    {
        array_push($this->_atom->images, [
            'href' => $href,
            'title' => $title,
            'type' => $type
        ]);

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
