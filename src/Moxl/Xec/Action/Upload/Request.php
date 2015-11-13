<?php

namespace Moxl\Xec\Action\Upload;

use Moxl\Xec\Action;
use Moxl\Stanza\Upload;

class Request extends Action
{
    private $_to;
    private $_name;
    private $_size;
    private $_type;

    public function request()
    {
        $this->store();
        Upload::request($this->_to, $this->_name, $this->_size, $this->_type);
    }

    public function setTo($to)
    {
        $this->_to = echapJid($to);
        return $this;
    }

    public function setName($name)
    {
        $this->_name = $name;
        return $this;
    }

    public function setSize($size)
    {
        $this->_size = $size;
        return $this;
    }

    public function setType($type)
    {
        $this->_type = $type;
        return $this;
    }

    public function handle($stanza, $parent = false) {
        if($stanza->slot) {
            $this->pack(
                array(
                    'get' => (string)$stanza->slot->get,
                    'put' => (string)$stanza->slot->put
                )
            );
            $this->deliver();
        }
    }

    public function error($error) {
        $this->pack($this->_to);
        $this->deliver();
    }
}
