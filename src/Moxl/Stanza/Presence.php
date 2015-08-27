<?php

namespace Moxl\Stanza;

class Presence {
    /*
     * The presence builder
     */
    static function maker($to = false, $status = false, $show = false, $priority = 0, $type = false)
    {
        $session = \Sessionx::start();

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $root = $dom->createElementNS('jabber:client', 'presence');
        $dom->appendChild($root);

        $root->setAttribute('from', $session->user.'@'.$session->host.'/'.$session->resource);
        $root->setAttribute('id', $session->id);

        if($to != false) {
            $root->setAttribute('to', $to);
        }

        if($type != false) {
            $root->setAttribute('type', $type);
        }

        if($status != false) {
            $status = $dom->createElement('status', $status);
            $root->appendChild($status);
        }

        if($show != false) {
            $show = $dom->createElement('show', $show);
            $root->appendChild($show);
        }

        if($priority != 0) {
            $priority = $dom->createElement('priority', $priority);
            $root->appendChild($priority);
        }

        $c = $dom->createElementNS('http://jabber.org/protocol/caps', 'c');
        $c->setAttribute('hash', 'sha-1');
        $c->setAttribute('node', 'http://moxl.movim.eu/');
        $c->setAttribute('ext', 'pmuc-v1 share-v1 voice-v1 video-v1 camera-v1');
        $c->setAttribute('ver', \Moxl\Utils::generateCaps());
        $root->appendChild($c);

        return $dom->saveXML($dom->documentElement);
    }

    /*
     * Simple presence without parameters
     */
    static function simple()
    {
        $xml = self::maker(false, false, false, false, false);
        \Moxl\API::request($xml);
    }

    /*
     * Subscribe to someone presence
     */
    static function unavailable($to = false, $status = false, $type = false)
    {
        $xml = self::maker($to, $status, false, false, 'unavailable');
        \Moxl\API::request($xml, $type);
    }

    /*
     * Subscribe to someone presence
     */
    static function subscribe($to, $status)
    {
        $xml = self::maker($to, $status, false, false, 'subscribe');
        \Moxl\API::request($xml);
    }

    /*
     * Unsubscribe to someone presence
     */
    static function unsubscribe($to, $status)
    {
        $xml = self::maker($to, $status, false, false, 'unsubscribe');
        \Moxl\API::request($xml);
    }

    /*
     * Accept someone presence \Moxl\API::request
     */
    static function subscribed($to)
    {
        $xml = self::maker($to, false, false, false, 'subscribed');
        \Moxl\API::request($xml);
    }

    /*
     * Refuse someone presence \Moxl\API::request
     */
    static function unsubscribed($to)
    {
        $xml = self::maker($to, false, false, false, 'unsubscribed');
        \Moxl\API::request($xml);
    }

    /*
     * Enter a chat room
     */
    static function muc($to, $nickname = false)
    {
        $session = \Sessionx::start();

        if($nickname == false)
            $nickname = $session->user;

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $presence = $dom->createElementNS('jabber:client', 'presence');
        $dom->appendChild($root);

        $presence->setAttribute('from', $session->user.'@'.$session->host.'/'.$session->resource);
        $presence->setAttribute('id', $session->id);
        $presence->setAttribute('to', $to.'/'.$nickname);

        $presence->appendChild($dom->createElementNS('http://jabber.org/protocol/muc', 'x'));

        \Moxl\API::request($dom->saveXML($dom->documentElement));
    }

    /*
     * Go away
     */
    static function away($status)
    {
        $xml = self::maker(false, $status, 'away', false, false);
        \Moxl\API::request($xml);
    }

    /*
     * Go chatting
     */
    static function chat($status)
    {
        $xml = self::maker(false, $status, 'chat', false, false);
        \Moxl\API::request($xml);
    }

    /*
     * Do not disturb
     */
    static function DND($status)
    {
        $xml = self::maker(false, $status, 'dnd', false, false);
        \Moxl\API::request($xml);
    }

    /*
     * eXtended Away
     */
    static function XA($status)
    {
        $xml = self::maker(false, $status, 'xa', false, false);
        \Moxl\API::request($xml);
    }
}
