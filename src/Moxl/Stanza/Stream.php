<?php

namespace Moxl\Stanza;

class Stream {
    static function init($to)
    {
        $xml = '<stream:stream xmlns:stream="http://etherx.jabber.org/streams" version="1.0" xmlns="jabber:client" to="'.$to.'" >';
        \Moxl\API::request($xml);
    }

    static function end()
    {
        $xml = '</stream:stream>';
        \Moxl\API::request($xml);
    }

    static function bindSet($resource)
    {
        $xml = '
            <bind xmlns="urn:ietf:params:xml:ns:xmpp-bind">
                <resource>'.$resource.'</resource>
            </bind>';
        $xml = \Moxl\API::iqWrapper($xml, false, 'set');
        \Moxl\API::request($xml);
    }
    
    static function sessionStart($to)
    {
        $xml = '
            <session xmlns="urn:ietf:params:xml:ns:xmpp-session"/>';
        $xml = \Moxl\API::iqWrapper($xml, $to, 'set');
        \Moxl\API::request($xml);
    }
}
