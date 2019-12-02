<?php

namespace Moxl\Stanza;

class Jingle
{
    public static function sessionPropose($to, $id)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $message = $dom->createElementNS('jabber:client', 'message');
        $message->setAttribute('to', $to);
        $dom->appendChild($message);

        $propose = $dom->createElementNS('urn:xmpp:jingle-message:0', 'propose');
        $propose->setAttribute('id', $id);
        $message->appendChild($propose);

        $description = $dom->createElementNS('urn:xmpp:jingle:apps:rtp:1', 'description');
        $description->setAttribute('media', 'video');
        $propose->appendChild($description);

        \Moxl\API::request($dom->saveXML($dom->documentElement));
    }

    public static function sessionAccept($to, $id)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $message = $dom->createElementNS('jabber:client', 'message');
        $message->setAttribute('to', $to);
        $dom->appendChild($message);

        $propose = $dom->createElementNS('urn:xmpp:jingle-message:0', 'accept');
        $propose->setAttribute('id', $id);
        $message->appendChild($propose);

        \Moxl\API::request($dom->saveXML($dom->documentElement));
    }

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
