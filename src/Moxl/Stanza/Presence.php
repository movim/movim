<?php

namespace Moxl\Stanza;

use DOMElement;
use Movim\Session;

class Presence
{
    /*
     * The presence builder
     */
    public static function maker(
        $to = false,
        $status = false,
        $show = false,
        int $priority = 0,
        $type = false,
        bool $muc = false,
        bool $mam = false,
        bool $mujiPreparing = false,
        ?DOMElement $muji = null,
        $last = 0
    ) {
        $session = Session::instance();

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $root = $dom->createElementNS('jabber:client', 'presence');
        $dom->appendChild($root);

        $me = me();

        if ($me && $me->session) {
            $root->setAttribute('from', $me->id . '/' . $me->session->resource);
        }

        $root->setAttribute('id', $session->get('id'));

        if ($to != false) {
            $root->setAttribute('to', $to);
        }

        if ($type != false) {
            $root->setAttribute('type', $type);
        }

        if ($status != false) {
            $status = $dom->createElement('status', $status);
            $root->appendChild($status);
        }

        if ($show != false) {
            $show = $dom->createElement('show', $show);
            $root->appendChild($show);
        }

        if ($priority != 0) {
            $priority = $dom->createElement('priority', $priority);
            $root->appendChild($priority);
        }

        if ($muji != null) {
            $root->append($dom->importNode($muji, true));
        }

        // https://xmpp.org/extensions/xep-0319.html#last-interact
        if ($last > 0) {
            $timestamp = time() - $last;
            $idle = $dom->createElementNS('urn:xmpp:idle:1', 'idle');
            $idle->setAttribute('since', gmdate('c', $timestamp));
            $root->appendChild($idle);
        }

        if ($mujiPreparing) {
            $muji = $dom->createElementNS('urn:xmpp:jingle:muji:0', 'muji');
            $muji->appendChild($dom->createElement('preparing'));
            $root->appendChild($muji);
        }

        if ($muc) {
            $x = $dom->createElementNS('http://jabber.org/protocol/muc', 'x');

            if ($mam) {
                $history = $dom->createElement('history');
                $history->setAttribute('maxchars', 0);
                $x->appendChild($history);
            }

            $root->appendChild($x);
        }

        $c = $dom->createElementNS('urn:xmpp:caps', 'c');
        $hash = $dom->createElement('hash', \Moxl\Utils::getOwnCapabilityHash());
        $hash->setAttribute('xmlns', 'urn:xmpp:hashes:2');
        $hash->setAttribute('algo', \Moxl\Utils::CAPABILITY_HASH_ALGORITHM);

        $c->appendChild($hash);

        $root->appendChild($c);

        $c = $dom->createElementNS('http://jabber.org/protocol/caps', 'c');
        $c->setAttribute('hash', 'sha-1');
        $c->setAttribute('node', 'https://movim.eu/');
        $c->setAttribute('ver', \Moxl\Utils::generateCaps());
        $root->appendChild($c);

        return $dom->saveXML($dom->documentElement);
    }

    /*
     * Simple presence without parameters
     */
    public static function simple()
    {
        \Moxl\API::request(self::maker());
    }

    /*
     * Subscribe to someone presence
     */
    public static function unavailable($to = false, $status = false, $type = false)
    {
        \Moxl\API::request(self::maker($to, $status, type: 'unavailable'));
    }

    /*
     * Subscribe to someone presence
     */
    public static function subscribe($to, $status)
    {
        \Moxl\API::request(self::maker($to, $status, type: 'subscribe'));
    }

    /*
     * Unsubscribe to someone presence
     */
    public static function unsubscribe($to, $status)
    {
        \Moxl\API::request(self::maker($to, $status, type: 'unsubscribe'));
    }

    /*
     * Accept someone presence \Moxl\API::request
     */
    public static function subscribed($to)
    {
        \Moxl\API::request(self::maker($to, type: 'subscribed'));
    }

    /*
     * Refuse someone presence \Moxl\API::request
     */
    public static function unsubscribed($to)
    {
        \Moxl\API::request(self::maker($to, type: 'unsubscribed'));
    }

    /*
     * Enter a chat room
     */
    public static function muc($to, $nickname = false, $mam = false, $mujiPreparing = false, ?DOMElement $muji = null)
    {
        \Moxl\API::request(self::maker($to . '/' . $nickname, muc: true, mam: $mam, mujiPreparing: $mujiPreparing, muji: $muji));
    }

    /*
     * Go away
     */
    public static function away($status = false, $last = 0)
    {
        \Moxl\API::request(self::maker(false, status: $status, show: 'away', last: $last));
    }

    /*
     * Go chatting
     */
    public static function chat($status = false)
    {
        \Moxl\API::request(self::maker(false, status: $status, show: 'chat'));
    }

    /*
     * Do not disturb
     */
    public static function DND($status = false)
    {
        \Moxl\API::request(self::maker(false, status: $status, show: 'dnd'));
    }

    /*
     * eXtended Away
     */
    public static function XA($status = false)
    {
        \Moxl\API::request(self::maker(false, status: $status, show: 'xa'));
    }
}
