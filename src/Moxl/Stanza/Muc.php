<?php

namespace Moxl\Stanza;

class Muc {
    static function message($to, $content)
    {
        $session = \Sessionx::start();
        $xml = '
            <message to="'.str_replace(' ', '\40', $to).'" type="groupchat" id="'.$session->id.'">
                <body>'.$content.'</body>
            </message>';
        \Moxl\API::request($xml);
    }

    static function setSubject($to, $subject)
    {
        $session = \Sessionx::start();
        $xml = '
            <message to="'.str_replace(' ', '\40', $to).'" type="groupchat" id="'.$session->id.'">
                <subject>'.$subject.'</subject>
            </message>';
        \Moxl\API::request($xml);
    }

    static function getConfig($to)
    {
        $xml = '
            <query xmlns="http://jabber.org/protocol/muc#owner"/>';
        $xml = \Moxl\API::iqWrapper($xml, $to, 'get');
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
