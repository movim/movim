<?php

namespace Moxl\Stanza;

function jingleSessionInitiate($to, $offer) {
    $xml = $offer;
    $xml = \Moxl\iqWrapper($xml, $to, 'set');
    \Moxl\request($xml);
}

function jingleSessionTerminate($to, $sid, $reason) {
    $xml = 
    '<jingle xmlns="urn:xmpp:jingle:1"
          action="session-terminate"
          sid="'.$sid.'">
        <reason>
            <'.$reason.'/>
        </reason>
    </jingle>';
    $xml = \Moxl\iqWrapper($xml, $to, 'set');
    \Moxl\request($xml);
}
