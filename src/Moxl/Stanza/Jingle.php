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

        return $dom;
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

        return $dom;
    }

    public static function messageRinging(string $to, string $id)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $message = $dom->createElementNS('jabber:client', 'message');
        $message->setAttribute('to', $to);
        $dom->appendChild($message);

        $ringing = $dom->createElementNS('urn:xmpp:jingle-message:0', 'ringing');
        $ringing->setAttribute('id', $id);
        $message->appendChild($ringing);

        return $dom;
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

        return $dom;
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

        return $dom;
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

        return $dom;
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

        return $dom;
    }

    public static function sessionTerminate($sid, $value)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $jingle = $dom->createElementNS('urn:xmpp:jingle:1', 'jingle');
        $jingle->setAttribute('action', 'session-terminate');
        $jingle->setAttribute('sid', $sid);

        $reason = $dom->createElement('reason');
        $jingle->appendChild($reason);

        $item = $dom->createElement($value);
        $reason->appendChild($item);

        return $jingle;
    }

    public static function sessionMute($sid, $name = false)
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

        return $jingle;
    }

    public static function sessionUnmute($sid, $name = false)
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

        return $jingle;
    }
}
