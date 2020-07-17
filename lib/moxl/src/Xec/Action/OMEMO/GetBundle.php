<?php

namespace Moxl\Xec\Action\OMEMO;

use Moxl\Xec\Action;
use Moxl\Stanza\OMEMO;

class GetBundle extends Action
{
    private $_to;
    private $_id;

    public function request()
    {
        $this->store();
        OMEMO::getBundle(
            $this->_to,
            $this->_id
        );
    }

    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }

    public function setId($id)
    {
        $this->_id = $id;
        return $this;
    }

    public function handle($stanza, $parent = false)
    {
        $bd = new \Modl\Bundle;
        $bd->set($stanza->pubsub->items->item, $this->_to, $this->_id);

        $this->pack($bd);
        $this->deliver();
    }
}
