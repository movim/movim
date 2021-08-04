<?php

namespace Moxl\Xec\Action\Microblog;

use Moxl\Stanza\Pubsub;
use Moxl\Stanza\PubsubAtom;
use Moxl\Xec\Action\Pubsub\Errors;
use Moxl\Xec\Action\Pubsub\GetItem;

class CommentPublish extends Errors
{
    protected $_to;
    protected $_node;
    protected $_parentid;
    protected $_commentnodeid;

    protected $_atom;

    public function __construct()
    {
        parent::__construct();
        $this->_atom = new PubsubAtom;
    }

    public function request()
    {
        $this->store();
        Pubsub::postPublish($this->_to, $this->_node, $this->_atom);
    }

    public function setTo($to)
    {
        $this->_to = $to;
        $this->_atom->to = $to;
        return $this;
    }

    public function setCommentNodeId($commentnodeid)
    {
        $this->_commentnodeid = $commentnodeid;
        $this->_node = 'urn:xmpp:microblog:0:comments/'.$this->_commentnodeid;
        $this->_atom->node = $this->_node;
        return $this;
    }

    public function setTitle($title)
    {
        $this->_atom->title = $title;
        return $this;
    }

    public function setContent($content)
    {
        $this->_atom->content = $content;
        return $this;
    }

    public function setName($name)
    {
        $this->_atom->name = $name;
        return $this;
    }

    public function setFrom($from)
    {
        $this->_atom->jid = $from;
        return $this;
    }

    public function handle($stanza, $parent = false)
    {
        $g = new GetItem;
        $g->setTo($this->_to)
          ->setNode($this->_node)
          ->setId($this->_atom->id)
          ->setParentId($this->_parentid)
          ->request();

        $this->pack(($this->_atom->title === '♥'));
        $this->deliver();
    }

    public function error()
    {
        $this->deliver();
    }
}
