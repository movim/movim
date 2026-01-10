<?php

namespace Moxl\Xec\Payload;

use App\User;
use Moxl\Xec\Payload\Packet;
use Movim\Widget\Wrapper;

abstract class Payload
{
    protected ?string $method = null;
    protected ?Packet $packet = null;

    /**
     * Constructor of class Payload.
     *
     * @return void
     */
    public function __construct(
        protected ?User $me = null,
        protected ?string $sessionId = null // Fallback when the user is not set
    ) {
        $this->packet = new Packet;
    }

    public function attachUser(?User $user = null)
    {
        $this->me = $user;
    }

    public function attachSession(?string $sessionId = null)
    {
        $this->sessionId = $sessionId;
    }

    public function iq(?\DOMNode $xml = null, ?string $to = null, ?string $type = null, $id = false)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $iq = $dom->createElementNS('jabber:client', 'iq');
        $dom->appendChild($iq);

        if ($this->me?->session?->resource) {
            $iq->setAttribute(
                'from',
                $this->me->id . '/' . $this->me->session->resource
            );
        }

        if ($to != null) {
            $iq->setAttribute('to', $to);
        }

        if ($type != null) {
            $iq->setAttribute('type', $type);
        }

        global $language;

        if ($id == false) {
            $id = linker($this->sessionId)->session->get('id');
        }
        $iq->setAttribute('id', $id);

        if (isset($language)) {
            $iq->setAttribute('xml:lang', $language);
        }

        if ($xml != false) {
            $xml = $dom->importNode($xml, true);
            $iq->appendChild($xml);
        }

        $this->send($dom);
    }

    public function send(?\DOMDocument $dom = null)
    {
        linker($this->sessionId ?? $this->me->session->id)->writeXMPP(
            $dom->saveXML($dom->documentElement)
        );
    }

    /**
     * Prepare the packet
     *
     * @return void
     */
    final public function prepare(\SimpleXMLElement $stanza, ?\SimpleXMLElement $parent = null)
    {
        $this->packet->from = ($parent === null)
            ? bareJid((string)$stanza->attributes()->from)
            : bareJid((string)$parent->attributes()->from);
    }

    /**
     * Set the content of the packet
     *
     * @return void
     */
    final public function pack($content, ?string $from = null)
    {
        $this->packet->pack($content, $from);
    }

    /**
     * Deliver the packet
     *
     * @return void
     */
    final public function deliver()
    {
        $action_ns = 'Moxl\Xec\Action';

        if (
            get_parent_class($this) == $action_ns
            || get_parent_class(get_parent_class($this)) == $action_ns
        ) {
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

        Wrapper::getInstance()->iterate(
            key: $key,
            packet: $this->packet,
            user: $this->me,
            sessionId: $this->sessionId
        );
    }

    /**
     * Send an event to Movim
     *
     * @return void
     */
    final public function event(string $key)
    {
        Wrapper::getInstance()->iterate(
            key: $key,
            packet: $this->packet,
            user: $this->me,
            sessionId: $this->sessionId
        );
    }

    /**
     * Set a specific method for the packet to specialize the key
     *
     * @return void
     */
    final public function method(string $method)
    {
        $this->method = strtolower($method);
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null) {}
}
