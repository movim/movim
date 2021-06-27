<?php

// TODO Remove me

namespace Moxl\Xec\Payload;

class OMEMOMovim extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $from   = (string)$parent->attributes()->from;

        $bd = new \Modl\Bundle;
        $bd->set($stanza->items->item, $from);

        if ($bd->id) {
            $this->pack($bd);
            $this->deliver();
        }
    }
}
