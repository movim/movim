<?php

namespace Moxl\Stanza;

class PubsubSubscription
{
    private static function generateId($server, $jid, $node)
    {
        $id = "";
        $id .= $server.'<';
        $id .= $node.'<';
        $id .= $jid;

        return sha1($id);
    }

    static function listAdd($server, $jid, $node, $title)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');

        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub', 'pubsub');

        $publish = $dom->createElement('publish');
        $publish->setAttribute('node', 'urn:xmpp:pubsub:subscription');
        $pubsub->appendChild($publish);

        $item = $dom->createElement('item');
        $item->setAttribute('id', self::generateId($server, $jid, $node));
        $publish->appendChild($item);

        $subscription = $dom->createElement('subscription');
        $subscription->setAttribute('xmlns', 'urn:xmpp:pubsub:subscription:0');
        $subscription->setAttribute('server', $server);
        $subscription->setAttribute('node', $node);
        $item->appendChild($subscription);

        $title = $dom->createElement('title', $title);
        $subscription->appendChild($title);

        $xml = \Moxl\API::iqWrapper($pubsub, false, 'set');
        \Moxl\API::request($xml);

        /*$xml .= '
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
        \Moxl\API::request($xml);*/
    }

    static function listRemove($server, $jid, $node)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');

        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub', 'pubsub');

        $retract = $dom->createElement('retract');
        $retract->setAttribute('node', 'urn:xmpp:pubsub:subscription');
        $pubsub->appendChild($retract);

        $item = $dom->createElement('item');
        $item->setAttribute('id', self::generateId($server, $jid, $node));
        $retract->appendChild($item);

        $xml = \Moxl\API::iqWrapper($pubsub, false, 'set');
        \Moxl\API::request($xml);

        /*
        $xml .= '
            <pubsub xmlns="http://jabber.org/protocol/pubsub">
                <retract node="urn:xmpp:pubsub:subscription">
                  <item id="'.$id.'"/>
                </retract>
            </pubsub>
            ';
        $xml = \Moxl\API::iqWrapper($xml, false, 'set');
        \Moxl\API::request($xml);*/
    }

    static function listGetOwned() {
        $xml = '<pubsub xmlns="http://jabber.org/protocol/pubsub">
                <affiliations/>
            </pubsub>';

        $xml = \Moxl\API::iqWrapper($xml, false, 'get');
        \Moxl\API::request($xml);
    }
}
