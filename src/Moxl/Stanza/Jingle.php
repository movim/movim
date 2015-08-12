<?php

namespace Moxl\Stanza;

class Jingle {
    static function sessionInitiate($to, $offer) {
        $xml = $offer;
        $xml = \Moxl\API::iqWrapper($xml, $to, 'set');
        \Moxl\API::request($xml);
    }

    static function sessionTerminate($to, $sid, $reason) {
        $xml = 
        '<jingle xmlns="urn:xmpp:jingle:1"
              action="session-terminate"
              sid="'.$sid.'">
            <reason>
                <'.$reason.'/>
            </reason>
        </jingle>';
        $xml = \Moxl\API::iqWrapper($xml, $to, 'set');
        \Moxl\API::request($xml);
    }
}
