<?php

namespace Moxl\Stanza;

class PubsubSubscription
{
    private static function generateId($server, $jid, $node)
    {
        $id = '';
        $id .= $server.'<';
        $id .= $node.'<';
        $id .= $jid;

        return sha1($id);
    }

    public static function listAdd($server, $jid, $node, $title = null, $pepnode = 'urn:xmpp:pubsub:subscription')
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');

        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub', 'pubsub');

        $publish = $dom->createElement('publish');
        $publish->setAttribute('node', $pepnode);
        $pubsub->appendChild($publish);

        $item = $dom->createElement('item');
        $item->setAttribute('id', self::generateId($server, $jid, $node));
        $publish->appendChild($item);

        $subscription = $dom->createElement('subscription');
        $subscription->setAttribute('xmlns', 'urn:xmpp:pubsub:subscription:0');
        $subscription->setAttribute('server', $server);
        $subscription->setAttribute('node', $node);
        $item->appendChild($subscription);

        if ($title) {
            $title = $dom->createElement('title', $title);
            $subscription->appendChild($title);
        }

        $xml = \Moxl\API::iqWrapper($pubsub, false, 'set');
        \Moxl\API::request($xml);
    }

    public static function listRemove($server, $jid, $node, $pepnode = 'urn:xmpp:pubsub:subscription')
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');

        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub', 'pubsub');

        $retract = $dom->createElement('retract');
        $retract->setAttribute('node', $pepnode);
        $pubsub->appendChild($retract);

        $item = $dom->createElement('item');
        $item->setAttribute('id', self::generateId($server, $jid, $node));
        $retract->appendChild($item);

        $xml = \Moxl\API::iqWrapper($pubsub, false, 'set');
        \Moxl\API::request($xml);
    }
}
