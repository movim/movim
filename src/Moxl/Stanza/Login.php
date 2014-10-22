<?php

namespace Moxl\Stanza;

class Login {
    static function streamInit($to)
    {
        $xml = '
            <stream:stream xmlns:stream="http://etherx.jabber.org/streams" version="1.0" xmlns="jabber:client" to="'.$to.'" >';
        \Moxl\API::request($xml);
    }
}
