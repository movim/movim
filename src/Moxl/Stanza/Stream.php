<?php

namespace Moxl\Stanza;

class Stream {
    static function init($to)
    {
        $xml = '<stream:stream xmlns:stream="http://etherx.jabber.org/streams" version="1.0" xmlns="jabber:client" to="'.$to.'">';
        \Moxl\API::request($xml);
    }

    static function end()
    {
        $xml = '</stream:stream>';
        \Moxl\API::request($xml);
    }

    static function startTLS()
    {
        $xml = '<starttls xmlns="urn:ietf:params:xml:ns:xmpp-tls"/>';
        \Moxl\API::request($xml);
    }

    static function bindSet($resource)
    {
        $dom = new \DOMDocument('1.0', 'utf-8');
        $bind = $dom->createElementNS('urn:ietf:params:xml:ns:xmpp-bind', 'bind');
        $bind->appendChild($dom->createElement('resource', $resource));

        $xml = \Moxl\API::iqWrapper($bind, false, 'set');
        \Moxl\API::request($xml);
    }

    static function sessionStart($to)
    {
        $dom = new \DOMDocument('1.0', 'utf-8');
        $session = $dom->createElementNS('urn:ietf:params:xml:ns:xmpp-session', 'session');
        $xml = \Moxl\API::iqWrapper($session, $to, 'set');
        \Moxl\API::request($xml);
    }
}
