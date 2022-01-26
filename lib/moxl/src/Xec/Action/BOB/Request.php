<?php

namespace Moxl\Xec\Action\BOB;

use Moxl\Xec\Action;
use Moxl\Stanza\BOB;
use Movim\Image;

class Request extends Action
{
    protected $_to;
    protected $_cid;
    protected $_resource;

    public function request()
    {
        $this->store();
        BOB::request($this->_to.'/'.$this->_resource, $this->_cid);
    }

    public function handle($stanza, $parent = false)
    {
        $data = (string)$stanza->data;

        $p = new Image;
        $p->fromBase($data);
        $p->setKey($this->_cid);
        $p->save(false, false, 'png');

        $this->pack(['to' => $this->_to, 'cid' => $this->_cid]);
        $this->deliver();
    }
}
