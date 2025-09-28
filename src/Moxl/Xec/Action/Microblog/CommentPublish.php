<?php

namespace Moxl\Xec\Action\Microblog;

use App\Post;
use Moxl\Stanza\Pubsub;
use Moxl\Stanza\PubsubAtom;
use Moxl\Xec\Action;
use Moxl\Xec\Action\Pubsub\GetItem;

class CommentPublish extends Action
{
    protected $_to;
    protected $_node;
    protected $_parentid;

    protected PubsubAtom $_atom;

    public function __construct()
    {
        parent::__construct();
        $this->_atom = new PubsubAtom;
    }

    public function request()
    {
        $this->store();
        Pubsub::postPublish($this->_to, $this->_node, $this->_atom, false);
    }

    public function setTo($to)
    {
        $this->_to = $to;
        $this->_atom->to = $to;
        return $this;
    }

    public function setId(string $id)
    {
        $this->_node = Post::COMMENTS_NODE . '/' . $id;
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

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        /*$g = new GetItem;
        $g->setTo($this->_to)
            ->setNode($this->_node)
            ->setId($this->_atom->id)
            ->setParentId($this->_parentid)
            ->request();*/

        $this->pack(($this->_atom->title === '♥'));
        $this->deliver();
    }

    public function error(string $errorId, ?string $message = null)
    {
        $this->deliver();
    }
}
