<?php

namespace Moxl\Stanza;

use Movim\Session;

class Presence
{
    /*
     * The presence builder
     */
    public static function maker($to = false, $status = false, $show = false, $priority = 0, $type = false, $muc = false, $mam = false, $last = 0)
    {
        $session = Session::start();

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $root = $dom->createElementNS('jabber:client', 'presence');
        $dom->appendChild($root);

        $me = \App\User::me();

        if ($me && $me->session) {
            $root->setAttribute('from', $me->id.'/'.$me->session->resource);
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

        // https://xmpp.org/extensions/xep-0319.html#last-interact
        if ($last > 0) {
            $timestamp = time() - $last;
            $idle = $dom->createElementNS('urn:xmpp:idle:1', 'idle');
            $idle->setAttribute('since', gmdate('c', $timestamp));
            $root->appendChild($idle);
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
        $xml = self::maker(false, false, false, false, false);
        \Moxl\API::request($xml);
    }

    /*
     * Subscribe to someone presence
     */
    public static function unavailable($to = false, $status = false, $type = false)
    {
        $xml = self::maker($to, $status, false, false, 'unavailable');
        \Moxl\API::request($xml, $type);
    }

    /*
     * Subscribe to someone presence
     */
    public static function subscribe($to, $status)
    {
        $xml = self::maker($to, $status, false, false, 'subscribe');
        \Moxl\API::request($xml);
    }

    /*
     * Unsubscribe to someone presence
     */
    public static function unsubscribe($to, $status)
    {
        $xml = self::maker($to, $status, false, false, 'unsubscribe');
        \Moxl\API::request($xml);
    }

    /*
     * Accept someone presence \Moxl\API::request
     */
    public static function subscribed($to)
    {
        $xml = self::maker($to, false, false, false, 'subscribed');
        \Moxl\API::request($xml);
    }

    /*
     * Refuse someone presence \Moxl\API::request
     */
    public static function unsubscribed($to)
    {
        $xml = self::maker($to, false, false, false, 'unsubscribed');
        \Moxl\API::request($xml);
    }

    /*
     * Enter a chat room
     */
    public static function muc($to, $nickname = false, $mam = false)
    {
        $xml = self::maker($to.'/'.$nickname, false, false, false , false, true, $mam);
        \Moxl\API::request($xml);
    }

    /*
     * Go away
     */
    public static function away($status = false, $last = 0)
    {
        $xml = self::maker(false, $status, 'away', false, false, false, false, $last);
        \Moxl\API::request($xml);
    }

    /*
     * Go chatting
     */
    public static function chat($status = false)
    {
        $xml = self::maker(false, $status, 'chat', false, false);
        \Moxl\API::request($xml);
    }

    /*
     * Do not disturb
     */
    public static function DND($status = false)
    {
        $xml = self::maker(false, $status, 'dnd', false, false);
        \Moxl\API::request($xml);
    }

    /*
     * eXtended Away
     */
    public static function XA($status = false)
    {
        $xml = self::maker(false, $status, 'xa', false, false);
        \Moxl\API::request($xml);
    }
}
