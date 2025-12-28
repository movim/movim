<?php

namespace Moxl\Xec;

use App\User;
use Moxl\Xec\Payload\Payload;
use Movim\Session;
use Movim\Widget\Base;

abstract class Action extends Payload
{
    protected $stanzaId;
    protected ?User $me;

    public function __construct(?Base $widget = null)
    {
        $this->me = $widget?->me;
        return parent::__construct();
    }

    final public function store(?string $customId = null)
    {
        $session = Session::instance();

        // Generating the iq key.
        $this->stanzaId = $customId ?? \generateKey(12);

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

    public function error(string $errorId, ?string $message = null) {}

    abstract public function request();
}
