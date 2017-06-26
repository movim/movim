<?php

namespace Moxl\Xec\Action\Disco;

use Moxl\Xec\Action;
use Moxl\Stanza\Disco;
use Moxl\Xec\Action\Disco\Items;

class Request extends Action
{
    private $_node;
    private $_to;

    // Excluded nodes
    private $_excluded = [
        'http://www.android.com/gtalk/client/caps#1.1'
    ];

    public function request()
    {
        $this->store();

        if(!in_array($this->_node, $this->_excluded)) {
            Disco::request($this->_to, $this->_node);
        }
    }

    public function setNode($node)
    {
        $this->_node = $node;
        return $this;
    }

    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }

    public function handle($stanza, $parent = false)
    {
        $c = new \Modl\Caps;

        if(isset($this->_node)) {
            $c->set($stanza, $this->_node);
        } else {
            $c->set($stanza, $this->_to);
        }

        $id = new \Modl\ItemDAO;
        $i = $id->getJid($this->_to);

        if(isset($stanza->query->x) && isset($i)) {
            $i->setMetadata($stanza->query->x);
            $id->set($i);
        }

        if($c->category == 'conference'
        && $c->type == 'text'
        && strpos($this->_to, '@') === false) {
            $c = new Items;
            $c->setTo($this->_to)
              ->request();
        }

        if(
            $c->node != ''
         && $c->category != ''
         && $c->type != ''
         && $c->name != '') {
            $cd = new \Modl\CapsDAO;
            $cd->set($c);
            $this->pack($c);
            $this->deliver();
        }
    }
}
