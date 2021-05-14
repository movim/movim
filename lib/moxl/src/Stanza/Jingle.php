<?php

namespace Moxl\Stanza;

class Jingle
{
    public static function sessionPropose($to, $id, $withVideo = false)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $message = $dom->createElementNS('jabber:client', 'message');
        $message->setAttribute('to', $to);
        $dom->appendChild($message);

        $propose = $dom->createElementNS('urn:xmpp:jingle-message:0', 'propose');
        $propose->setAttribute('id', $id);
        $message->appendChild($propose);

        if ($withVideo) {
            $description = $dom->createElementNS('urn:xmpp:jingle:apps:rtp:1', 'description');
            $description->setAttribute('media', 'video');
            $propose->appendChild($description);
        }

        $description = $dom->createElementNS('urn:xmpp:jingle:apps:rtp:1', 'description');
        $description->setAttribute('media', 'audio');
        $propose->appendChild($description);

        \Moxl\API::request($dom->saveXML($dom->documentElement));
    }

    public static function sessionAccept($id)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $message = $dom->createElementNS('jabber:client', 'message');
        $dom->appendChild($message);

        $accept = $dom->createElementNS('urn:xmpp:jingle-message:0', 'accept');
        $accept->setAttribute('id', $id);
        $message->appendChild($accept);

        \Moxl\API::request($dom->saveXML($dom->documentElement));
    }

    public static function sessionProceed($to, $id)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $message = $dom->createElementNS('jabber:client', 'message');
        $message->setAttribute('to', $to);
        $dom->appendChild($message);

        $proceed = $dom->createElementNS('urn:xmpp:jingle-message:0', 'proceed');
        $proceed->setAttribute('id', $id);
        $message->appendChild($proceed);

        \Moxl\API::request($dom->saveXML($dom->documentElement));
    }

    public static function sessionReject($id, $to = false)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $message = $dom->createElementNS('jabber:client', 'message');
        if ($to) {
            $message->setAttribute('to', $to);
        }
        $dom->appendChild($message);

        $proceed = $dom->createElementNS('urn:xmpp:jingle-message:0', 'reject');
        $proceed->setAttribute('id', $id);
        $message->appendChild($proceed);

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

    public static function sessionMute($to, $sid, $name = false)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $jingle = $dom->createElementNS('urn:xmpp:jingle:1', 'jingle');
        $jingle->setAttribute('action', 'session-info');
        $jingle->setAttribute('sid', $sid);

        $mute = $dom->createElement('mute');
        $mute->setAttribute('xmlns', 'urn:xmpp:jingle:apps:rtp:info:1');

        if ($name) {
            $mute->setAttribute('name', substr($name, 3));
        }

        $jingle->appendChild($mute);

        $xml = \Moxl\API::iqWrapper($jingle, $to, 'set');
        \Moxl\API::request($xml);
    }

    public static function sessionUnmute($to, $sid, $name = false)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $jingle = $dom->createElementNS('urn:xmpp:jingle:1', 'jingle');
        $jingle->setAttribute('action', 'session-info');
        $jingle->setAttribute('sid', $sid);

        $mute = $dom->createElement('unmute');
        $mute->setAttribute('xmlns', 'urn:xmpp:jingle:apps:rtp:info:1');

        if ($name) {
            $mute->setAttribute('name', substr($name, 3));
        }

        $jingle->appendChild($mute);

        $xml = \Moxl\API::iqWrapper($jingle, $to, 'set');
        \Moxl\API::request($xml);
    }

    public static function unknownSession($to, $id)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $error = $dom->createElement('error');
        $error->setAttribute('type', 'cancel');

        $fni = $dom->createElementNS('urn:xmpp:jingle:errors:1', 'unknown-session');
        $error->appendChild($fni);

        $xml = \Moxl\API::iqWrapper($error, $to, 'error', $id);
        \Moxl\API::request($xml);
    }
}
