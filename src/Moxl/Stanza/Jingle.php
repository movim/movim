<?php

namespace Moxl\Stanza;

class Jingle
{
    /**
     * XEP-0353: Jingle Message Initiation
     */

    public static function messagePropose(string $to, string $id, bool $withVideo = false)
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

        \Moxl\API::sendDom($dom);
    }

    // Deprecated
    public static function messageAccept(string $id)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $message = $dom->createElementNS('jabber:client', 'message');
        $dom->appendChild($message);

        $accept = $dom->createElementNS('urn:xmpp:jingle:jingle-message:0', 'accept');
        $accept->setAttribute('id', $id);
        $message->appendChild($accept);

        \Moxl\API::sendDom($dom);
    }

    public static function messageProceed(string $to, string $id)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $message = $dom->createElementNS('jabber:client', 'message');
        $message->setAttribute('to', $to);
        $dom->appendChild($message);

        $proceed = $dom->createElementNS('urn:xmpp:jingle-message:0', 'proceed');
        $proceed->setAttribute('id', $id);
        $message->appendChild($proceed);

        \Moxl\API::sendDom($dom);
    }

    public static function messageRetract(string $to, string $id)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $message = $dom->createElementNS('jabber:client', 'message');
        $message->setAttribute('to', $to);
        $dom->appendChild($message);

        $retract = $dom->createElementNS('urn:xmpp:jingle-message:0', 'retract');
        $retract->setAttribute('id', $id);
        $message->appendChild($retract);

        $reason = $dom->createElementNS('urn:xmpp:jingle:1', 'reason');
        $retract->appendChild($reason);

        $reason->appendChild($dom->createElement('cancel'));
        $reason->appendChild($dom->createElement('text', 'Retracted'));

        \Moxl\API::sendDom($dom);
    }

    public static function messageFinish(string $to, string $id, string $reason)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $message = $dom->createElementNS('jabber:client', 'message');
        $message->setAttribute('to', $to);
        $dom->appendChild($message);

        $finish = $dom->createElementNS('urn:xmpp:jingle-message:0', 'finish');
        $finish->setAttribute('id', $id);
        $message->appendChild($finish);

        $jingleReason = $dom->createElementNS('urn:xmpp:jingle:1', 'reason');
        $finish->appendChild($jingleReason);

        $jingleReason->appendChild($dom->createElement($reason));
        $jingleReason->appendChild($dom->createElement('text', 'Success'));

        \Moxl\API::sendDom($dom);
    }

    public static function messageReject($id, $to = false)
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

        \Moxl\API::sendDom($dom);
    }

    public static function sessionInitiate(string $to, $jingle)
    {
        \Moxl\API::request(\Moxl\API::iqWrapper($jingle, $to, 'set'));
    }

    public static function contentAdd(string $to, $jingle)
    {
        \Moxl\API::request(\Moxl\API::iqWrapper($jingle, $to, 'set'));
    }

    public static function contentModify(string $to, $jingle)
    {
        \Moxl\API::request(\Moxl\API::iqWrapper($jingle, $to, 'set'));
    }

    public static function contentRemove(string $to, $jingle)
    {
        \Moxl\API::request(\Moxl\API::iqWrapper($jingle, $to, 'set'));
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

        \Moxl\API::request(\Moxl\API::iqWrapper($jingle, $to, 'set'));
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

        \Moxl\API::request(\Moxl\API::iqWrapper($jingle, $to, 'set'));
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

        \Moxl\API::request(\Moxl\API::iqWrapper($jingle, $to, 'set'));
    }

    public static function unknownSession($to, $id)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $error = $dom->createElement('error');
        $error->setAttribute('type', 'cancel');

        $us = $dom->createElement('unknown-session');
        $us->setAttribute('xmlns', 'urn:xmpp:jingle:errors:1');
        $error->appendChild($us);

        \Moxl\API::request(\Moxl\API::iqWrapper($error, $to, 'error', $id));
    }
}
