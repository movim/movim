<?php

namespace Moxl\Xec;

use Moxl\Utils;
use Moxl\Xec\Payload\Payload;

use Movim\Session;

abstract class Action extends Payload
{
    final public function store()
    {
        $session = Session::start();

        // Generating the iq key.
        $id = \generateKey(6);

        $session->set('id', $id);
        $session->set($id, $this, true);
        $session->clean();
    }

    public function __call($name, $args)
    {
        if (substr($name, 0, 3) == 'set') {
            $property = '_' . strtolower(substr($name, 3));
            $this->$property = $args[0];

            return $this;
        }
    }

    abstract public function request();
}
