<?php

namespace Moxl\Stanza;

class Stream
{
    public static function init($to)
    {
        $xml = '<stream:stream xmlns:stream="http://etherx.jabber.org/streams" version="1.0" xmlns="jabber:client" to="'.$to.'">';
        \Moxl\API::request($xml);
    }

    public static function end()
    {
        $xml = '</stream:stream>';
        \Moxl\API::request($xml);
    }

    public static function startTLS()
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $starttls = $dom->createElementNS('urn:ietf:params:xml:ns:xmpp-tls', 'starttls');
        $dom->appendChild($starttls);

        \Moxl\API::request($dom->saveXML($dom->documentElement));
    }

    public static function bindSet($resource)
    {
        $dom = new \DOMDocument('1.0', 'utf-8');
        $bind = $dom->createElementNS('urn:ietf:params:xml:ns:xmpp-bind', 'bind');
        $bind->appendChild($dom->createElement('resource', $resource));

        $xml = \Moxl\API::iqWrapper($bind, false, 'set');
        \Moxl\API::request($xml);
    }

    public static function sessionStart($to)
    {
        $dom = new \DOMDocument('1.0', 'utf-8');
        $session = $dom->createElementNS('urn:ietf:params:xml:ns:xmpp-session', 'session');
        $xml = \Moxl\API::iqWrapper($session, $to, 'set');
        \Moxl\API::request($xml);
    }
}
