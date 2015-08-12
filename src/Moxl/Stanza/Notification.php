<?php

namespace Moxl\Stanza;

class Notification {
    static function get($to)
    {
        $xml = '
            <pubsub xmlns="http://jabber.org/protocol/pubsub">
                <items node="urn:xmpp:inbox"/>
            </pubsub>';
        $xml = \Moxl\API::iqWrapper($xml, $to, 'get');
        \Moxl\API::request($xml);
    }

    static function itemDelete($to, $id)
    {
        $xml = '
            <pubsub xmlns="http://jabber.org/protocol/pubsub">
                <retract node="urn:xmpp:inbox" notify="true">
                    <item id="'.$id.'"/>
                </retract>
            </pubsub>';
        $xml = \Moxl\API::iqWrapper($xml, $to, 'set');
        \Moxl\API::request($xml);
    }
}
