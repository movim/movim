<?php

namespace Moxl\Stanza;

class Jingle
{
    public static function sessionInitiate($to, $offer)
    {
        $xml = \Moxl\API::iqWrapper($offer, $to, 'set');
        \Moxl\API::request($xml);
    }

    public static function sessionTerminate($to, $sid, $value)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $jingle = $dom->createElementNS('urn:xmpp:jingle:1', 'jingle');
        $jingle->setAttribute('action', 'session-terminate');
        $jingle->setAttribute('sid', $sid);

        $reason = $dom->createElement('reason');
        $jingle->appendChild($reason);

        $item = $dom->createElement($value);
        $reason->appendChild($item);

        $xml = \Moxl\API::iqWrapper($jingle, $to, 'set');
        \Moxl\API::request($xml);
    }
}
