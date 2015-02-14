<?php

namespace Moxl\Stanza;

class AdHoc {
    static function get($to)
    {
        $xml = '
          <query xmlns="http://jabber.org/protocol/disco#items"
                 node="http://jabber.org/protocol/commands"/>';
        $xml = \Moxl\API::iqWrapper($xml, $to, 'get');
        \Moxl\API::request($xml);
    }

    static function command($to, $node)
    {
        $xml = '
            <command xmlns="http://jabber.org/protocol/commands"
                node="'.$node.'"
                action="execute"/>';
        $xml = \Moxl\API::iqWrapper($xml, $to, 'set');
        \Moxl\API::request($xml);
    }

    static function submit($to, $node, $data, $sessionid)
    {
        $xmpp = new \FormtoXMPP();
        $stream = '
            <command xmlns="http://jabber.org/protocol/commands"
                   sessionid="'.$sessionid.'"
                   node="'.$node.'">
                <x xmlns="jabber:x:data" type="submit"></x>
            </command>';
        $xml = $xmpp->getXMPP($stream, $data)->asXML();
        $xml = \Moxl\API::iqWrapper(strstr($xml, '<command'), $to, 'set');
        \Moxl\API::request($xml);
    }
}
