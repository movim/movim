<?php

// http://xmpp.org/extensions/xep-0280.html

namespace Moxl\Stanza;

class Carbons {
    static function enable() {
        $xml = '
            <enable xmlns="urn:xmpp:carbons:2"/>
            ';

        $xml = \Moxl\API::iqWrapper($xml, false, 'set');

        \Moxl\API::request($xml);
    }

    static function disable() {
        $xml = '
            <disable xmlns="urn:xmpp:carbons:2"/>
            ';

        $xml = \Moxl\API::iqWrapper($xml, false, 'set');

        \Moxl\API::request($xml);
    }
}
