<?php

namespace Moxl\Xec;

use Moxl\Xec\Payload\Payload;
use Movim\Session;

abstract class Action extends Payload
{
    protected $stanzaId;

    final public function store(string $customId = null)
    {
        $session = Session::start();

        // Generating the iq key.
        $this->stanzaId = $customId ?? \generateKey(6);

        $session->set('id', $this->stanzaId);
        $session->set($this->stanzaId, $this, true);
        $session->clean();
    }

    public function __call($name, $args)
    {
        if (substr($name, 0, 3) == 'set') {
            $property = '_' . strtolower(substr($name, 3));

            if (array_key_exists(0, $args)) {
                $this->$property = $args[0];
            }

            return $this;
        }
    }

    abstract public function request();
}
