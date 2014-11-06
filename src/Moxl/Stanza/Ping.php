<?php

namespace Moxl\Stanza;

class Ping {
    static function server() {
        $session = \Sessionx::start();
        $xml = \Moxl\API::iqWrapper('<ping xmlns="urn:xmpp:ping"/>', $session->host, 'get');
        
        \Moxl\API::request($xml);
    }

    static function pong($to, $id) {
        $xml = '
            <iq type="result" xmlns="jabber:client" to="'.$to.'" id="'.$id.'">
                <ping xmlns="urn:xmpp:ping"/>
            </iq>';
        \Moxl\API::request($xml);
    }
}
