<?php

namespace Moxl\Stanza;

class Ping {
    static function server() {
        $session = \Sessionx::start();
        $xml = \Moxl\API::iqWrapper('<ping xmlns="urn:xmpp:ping"/>', $session->host, 'get');
        
        \Moxl\API::request($xml);
    }
}
