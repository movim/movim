<?php

namespace Moxl\Stanza;

function notificationGet($to)
{
    $xml = '
        <pubsub xmlns="http://jabber.org/protocol/pubsub">
            <items node="urn:xmpp:inbox"/>
        </pubsub>';
    $xml = \Moxl\iqWrapper($xml, $to, 'get');
    \Moxl\request($xml);
}

function notificationItemDelete($to, $id)
{
    $xml = '
        <pubsub xmlns="http://jabber.org/protocol/pubsub">
            <retract node="urn:xmpp:inbox" notify="true">
                <item id="'.$id.'"/>
            </retract>
        </pubsub>';
    $xml = \Moxl\iqWrapper($xml, $to, 'set');
    \Moxl\request($xml);
}
