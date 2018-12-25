<?php

namespace Moxl\Stanza;

class Bookmark
{
    static function get()
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub', 'pubsub');

        $items = $dom->createElement('items');
        $items->setAttribute('node', 'storage:bookmarks');
        $pubsub->appendChild($items);

        $xml = \Moxl\API::iqWrapper($pubsub, false, 'get');
        \Moxl\API::request($xml);
    }

    static function set($arr)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub', 'pubsub');

        $publish = $dom->createElement('publish');
        $pubsub->appendChild($publish);

        $item = $dom->createElement('item');
        $publish->appendChild($item);

        $storage = $dom->createElement('storage');
        $item->appendChild($storage);
        $storage->setAttribute('xmlns', 'storage:bookmarks');

        $publish->setAttribute('node', 'storage:bookmarks');
        $item->setAttribute('id', 'current');

        foreach ($arr as $elt) {
            switch ($elt['type']) {
                case 'conference':
                    $conference = $dom->createElement('conference');
                    $nick = $dom->createElement('nick', $elt['nick']);
                    $conference->appendChild($nick);
                    $conference->setAttribute('name', $elt['name']);
                    if ($elt['autojoin']) {
                        $conference->setAttribute('autojoin', $elt['autojoin']);
                    }
                    $conference->setAttribute('jid', $elt['jid']);

                    $storage->appendChild($conference);
                    break;
                /*case 'url':
                    $xml .= '
                        <url name="'.$elt['name'].'"
                             url="'.$elt['url'].'"/>';
                    break;*/
                case 'subscription':
                    $subscription = $dom->createElement('subscription');
                    $storage->appendChild($subscription);
                    $subscription->setAttribute('xmlns', 'urn:xmpp:pubsub:subscription:0');

                    $title = $dom->createElement('title', $elt['title']);
                    $subscription->appendChild($title);
                    $subscription->setAttribute('server', $elt['server']);
                    $subscription->setAttribute('node', $elt['node']);
                    $subscription->setAttribute('subid', $elt['subid']);

                    break;
            }
        }

        $xml = \Moxl\API::iqWrapper($pubsub, false, 'set');
        \Moxl\API::request($xml);
    }
}
