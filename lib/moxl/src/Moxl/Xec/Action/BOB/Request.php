<?php

namespace Moxl\Xec\Action\BOB;

use Moxl\Xec\Action;
use Moxl\Stanza\BOB;

use Movim\Picture;

class Request extends Action
{
    private $_to;
    private $_cid;
    private $_resource;

    public function request()
    {
        $this->store();
        BOB::request($this->_to.'/'.$this->_resource, $this->_cid);
    }

    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }

    public function setResource($resource)
    {
        $this->_resource = $resource;
        return $this;
    }

    public function setCid($cid)
    {
        $this->_cid = $cid;
        return $this;
    }

    public function setId($id)
    {
        $this->_id = $id;
        return $this;
    }

    public function handle($stanza, $parent = false) {
        $type = (string)$stanza->data->attributes()->type;
        $data = (string)$stanza->data;

        $p = new Picture;
        $p->fromBase($data);
        $p->set($this->_cid, 'png');

        $this->pack(['to' => $this->_to, 'cid' => $this->_cid]);
        $this->deliver();
    }
}
