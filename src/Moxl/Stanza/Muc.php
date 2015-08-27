<?php

namespace Moxl\Stanza;

use Moxl\Stanza\Message;

class Muc {
    static function message($to, $content)
    {
        Message::maker($to, $content, false, 'groupchat');
    }

    static function setSubject($to, $subject)
    {
        $session = \Sessionx::start();

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $message = $dom->createElementNS('jabber:client', 'message');
        $dom->appendChild($message);
        $message->setAttribute('to', str_replace(' ', '\40', $to));
        $message->setAttribute('id', $session->id);
        $message->setAttribute('type', 'groupchat');

        $message->appendChild($dom->createElement('subject', $subject));

        \Moxl\API::request($dom->saveXML($dom->documentElement));
    }

    static function getConfig($to)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('http://jabber.org/protocol/muc#owner', 'query');

        $xml = \Moxl\API::iqWrapper($query, $to, 'get');
        \Moxl\API::request($xml);
    }

    static function setConfig($to, $data)
    {
        $xmpp = new \FormtoXMPP();
        $stream = '
            <query xmlns="http://jabber.org/protocol/muc#owner">
                <x xmlns="jabber:x:data" type="submit"></x>
            </query>';

        $xml = $xmpp->getXMPP($stream, $data)->asXML();
        $xml = \Moxl\API::iqWrapper(strstr($xml, '<query'), $to, 'set');
        \Moxl\API::request($xml);
    }
}
