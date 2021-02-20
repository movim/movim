<?php

namespace Moxl\Xec\Action\OMEMO;

use Moxl\Xec\Action;
use Moxl\Stanza\OMEMO;

use App\Bundle;

class GetBundle extends Action
{
    protected $_to;
    protected $_id;

    public function request()
    {
        $this->store();
        OMEMO::getBundle(
            $this->_to,
            $this->_id
        );
    }

    public function handle($stanza, $parent = false)
    {
        $bd = new Bundle;
        $bd->set($this->_to, $this->_id, $stanza->pubsub->items->item->bundle);
        $bd->save();

        $this->pack($bd);
        $this->deliver();
    }
}
