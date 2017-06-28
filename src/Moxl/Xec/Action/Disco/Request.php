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
        // Caps
        $c = new \Modl\Caps;

        if(isset($this->_node)) {
            $c->set($stanza, $this->_node);
        } else {
            $c->set($stanza, $this->_to);
        }

        $cd = new \Modl\CapsDAO;
        $cd->set($c);

        // Info
        $ind = new \Modl\InfoDAO;
        $in = $ind->get($this->_to, $this->_node);

        if(!$in) {
            $in = new \Modl\Info;
        }

        $in->set($stanza);
        $ind->set($in);

        if($in->category == 'conference'
        && $in->type == 'text'
        && strpos($this->_to, '@') === false) {
            $c = new Items;
            $c->setTo($this->_to)
              ->request();
        }

        $this->pack([$this->_to, $this->_node]);
        $this->deliver();
    }
}
