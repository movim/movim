<?php

namespace Moxl\Stanza;

class PubsubSubscription
{
    static function listAdd($server, $jid, $node, $title)
    {
        $id = "";
        $id .= $server.'<';
        $id .= $node.'<';
        $id .= $jid;
        $id = sha1($id);

        $xml .= '
            <pubsub xmlns="http://jabber.org/protocol/pubsub">
                <publish node="urn:xmpp:pubsub:subscription">
                  <item id="'.$id.'">
                    <subscription xmlns="urn:xmpp:pubsub:subscription:0"
                        server="'.$server.'" node="'.$node.'">
                      <title>'.$title.'</title>
                    </subscription>
                  </item>
                </publish>
            </pubsub>
            ';
        $xml = \Moxl\API::iqWrapper($xml, false, 'set');
        \Moxl\API::request($xml);
    }

    static function listRemove($server, $jid, $node)
    {
        $id = "";
        $id .= $server.'<';
        $id .= $node.'<';
        $id .= $jid;
        $id = sha1($id);

        $xml .= '
            <pubsub xmlns="http://jabber.org/protocol/pubsub">
                <retract node="urn:xmpp:pubsub:subscription">
                  <item id="'.$id.'"/>
                </retract>
            </pubsub>
            ';
        $xml = \Moxl\API::iqWrapper($xml, false, 'set');
        \Moxl\API::request($xml);
    }

    static function listGet() {
        $xml = '<pubsub xmlns="http://jabber.org/protocol/pubsub">
                <items node="urn:xmpp:pubsub:subscription"/>
            </pubsub>';

        $xml = \Moxl\API::iqWrapper($xml, false, 'get');
        \Moxl\API::request($xml);
    }

    static function listGetOwned() {
        $xml = '<pubsub xmlns="http://jabber.org/protocol/pubsub">
                <affiliations/>
            </pubsub>';

        $xml = \Moxl\API::iqWrapper($xml, false, 'get');
        \Moxl\API::request($xml);
    }

    static function listGetFriends($to) {
        $xml = '
            <pubsub xmlns="http://jabber.org/protocol/pubsub">
                <items node="urn:xmpp:pubsub:subscription"/>
            </pubsub>';

        $xml = \Moxl\API::iqWrapper($xml, $to, 'get');
        \Moxl\API::request($xml);
    }
}
