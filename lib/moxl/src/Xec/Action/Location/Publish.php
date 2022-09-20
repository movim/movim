<?php

namespace Moxl\Xec\Action\Location;

use Moxl\Xec\Action;
use Moxl\Stanza\Location;

class Publish extends Action
{
    protected $_geo;

    public function request()
    {
        $this->store();
        Location::publish($this->_geo);
    }

    public function setGeo(array $geo)
    {
        $this->_geo  = $geo;
        return $this;
    }

    public function handle($stanza, $parent = false)
    {
        $this->deliver();
    }
}
