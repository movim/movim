<?php

namespace Moxl\Xec\Payload;

use Moxl\Xec\Payload\Packet;
use Movim\Widget\Wrapper;

abstract class Payload
{
    protected $method;
    protected $packet;

    /**
     * Constructor of class Payload.
     *
     * @return void
     */
    public function __construct()
    {
        $this->packet = new Packet;
    }

    /**
     * Prepare the packet
     *
     * @return void
     */
    final public function prepare($stanza, $parent = false)
    {
        $this->packet->from = ($parent === false)
            ? current(explode('/', (string)$stanza->attributes()->from))
            : current(explode('/', (string)$parent->attributes()->from));
    }

    /**
     * Deliver the packet
     *
     * @return void
     */
    final public function deliver($content = null, $from = null)
    {
        if ($content !== null) {
            $this->pack($content, $from);
        }

        $action_ns = 'Moxl\Xec\Action';
        if (get_parent_class($this) == $action_ns
        || get_parent_class(get_parent_class($this)) == $action_ns) {
            $class = str_replace([$action_ns, '\\'], ['', '_'], get_class($this));
            $key = strtolower(substr($class, 1));
        } else {
            $class = strtolower(get_class($this));
            $pos = strrpos($class, '\\');
            $key = substr($class, $pos + 1);
        }

        if ($this->method) {
            $key = $key . '_' . $this->method;
        }

        if (!empty($this->packet->from)) {
            \Utils::info('Package : "'.$key.'" from "'.$this->packet->from.'" fired');
        } else {
            \Utils::info('Package : "'.$key);
        }

        $this->event($key, $this->packet);
    }

    /**
     * Send an event to Movim
     *
     * @return void
     */
    final public function event($key, $packet = null)
    {
        $wrapper = Wrapper::getInstance();
        $wrapper->iterate($key, $packet ? $packet : $this->packet);
    }

    /**
     * Set a specific method for the packet to specialize the key
     *
     * @return void
     */
    final public function method($method)
    {
        $this->method = strtolower($method);
    }

    /**
     * Set the content of the packet
     *
     * @return void
     */
    final public function pack($content, $from = null)
    {
        $this->packet->content = $content;
        if ($from != null) {
            $this->packet->from = $from;
        }
    }

    public function handle($stanza, $parent = false)
    {
    }
}
