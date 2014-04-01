<?php

// http://xmpp.org/extensions/xep-0280.html

namespace Moxl\Stanza;

function enableCarbons() {
    $xml = '
        <enable xmlns="urn:xmpp:carbons:2"/>
        ';

    $xml = \Moxl\iqWrapper($xml, false, 'set');

    \Moxl\request($xml);
}

function disableCarbons() {
    $xml = '
        <disable xmlns="urn:xmpp:carbons:2"/>
        ';

    $xml = \Moxl\iqWrapper($xml, false, 'set');

    \Moxl\request($xml);
}
