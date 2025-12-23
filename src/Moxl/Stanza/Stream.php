<?php

namespace Moxl\Stanza;

class Stream
{
    public static function init(string $to, ?string $from = null)
    {
        $dom = new \DOMDocument('1.0', 'utf-8');
        $stream = $dom->createElement('stream:stream', ' ');
        $stream->setAttribute('xmlns', 'jabber:client');
        $stream->setAttribute('xmlns:stream', 'http://etherx.jabber.org/streams');
        $stream->setAttribute('version', '1.0');
        $stream->setAttribute('to', $to);

        if ($from != null) {
            $stream->setAttribute('from', $from);
        }

        $dom->appendChild($stream);

        \writeXMPP(substr($dom->saveXML($dom->documentElement), 0, -17));
    }

    public static function end()
    {
        $xml = '</stream:stream>';
        \writeXMPP($xml);
    }

    public static function startTLS()
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $starttls = $dom->createElementNS('urn:ietf:params:xml:ns:xmpp-tls', 'starttls');
        $dom->appendChild($starttls);

        return $dom;
    }

    public static function bindSet($resource)
    {
        $dom = new \DOMDocument('1.0', 'utf-8');
        $bind = $dom->createElementNS('urn:ietf:params:xml:ns:xmpp-bind', 'bind');
        $bind->appendChild($dom->createElement('resource', $resource));

        return $bind;
    }

    public static function bind2Set(string $mechanism, string $initialResponse, string $tag)
    {
        $dom = new \DOMDocument('1.0', 'utf-8');
        $authenticate = $dom->createElementNS('urn:xmpp:sasl:2', 'authenticate');
        $authenticate->setAttribute('mechanism', $mechanism);
        $authenticate->appendChild($dom->createElement('initial-response', base64_encode($initialResponse)));

        $bind = $dom->createElement('bind');
        $bind->setAttribute('xmlns', 'urn:xmpp:bind:0');
        $bind->appendChild($dom->createElement('tag', $tag));

        $enable = $dom->createElement('enable');
        $enable->setAttribute('xmlns', 'urn:xmpp:carbons:2');
        $bind->appendChild($enable);

        $userAgent = $dom->createElement('user-agent');
        $userAgent->setAttribute('id', generateUUID($tag . APP_VERSION . ' ' . BASE_URI));
        $userAgent->appendChild($dom->createElement('software', $tag));

        $authenticate->appendChild($userAgent);
        $authenticate->appendChild($bind);

        $dom->appendChild($authenticate);

        return $dom;
    }

    public static function saslChallenge(string $response)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $auth = $dom->createElementNS('urn:ietf:params:xml:ns:xmpp-sasl', 'response', $response);
        $dom->appendChild($auth);

        return $dom;
    }

    public static function sasl2Response(string $response)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $auth = $dom->createElementNS('urn:xmpp:sasl:2', 'response', $response);
        $dom->appendChild($auth);

        return $dom;
    }

    public static function sessionStart()
    {
        $dom = new \DOMDocument('1.0', 'utf-8');
        $session = $dom->createElementNS('urn:ietf:params:xml:ns:xmpp-session', 'session');

        return $session;
    }
}
