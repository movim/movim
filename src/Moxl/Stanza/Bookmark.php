<?php

namespace Moxl\Stanza;

class Bookmark {
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
        $publish->setAttribute('node', 'storage:bookmarks');
        $pubsub->appendChild($publish);

        $item = $dom->createElement('item');
        $item->setAttribute('id', 'current');
        $publish->appendChild($item);

        $storage = $dom->createElementNS('storage:bookmarks', 'storage');
        $item->appendChild($storage);

        foreach($arr as $elt) {
            switch ($elt['type']) {
                case 'conference':
                    $conference = $dom->createElement('conference');
                    $nick = $dom->createElement('nick', $elt['nick']);
                    $conference->appendChild($nick);
                    $conference->setAttribute('name', html_entity_decode($elt['name']));
                    $conference->setAttribute('autojoin', $elt['autojoin']);
                    $conference->setAttribute('jid', $elt['jid']);

                    $storage->appendChild($conference);
                    break;
                /*case 'url':
                    $xml .= '
                        <url name="'.$elt['name'].'"
                             url="'.$elt['url'].'"/>';
                    break;*/
                case 'subscription':
                    $subscription = $dom->createElementNS('urn:xmpp:pubsub:subscription:0', 'subcription');
                    $title = $dom->createElement('title', $elt['title']);
                    $subscription->appendChild($title);
                    $subscription->setAttribute('server', $elt['server']);
                    $subscription->setAttribute('node', $elt['node']);
                    $subscription->setAttribute('subid', $elt['subid']);

                    $storage->appendChild($subscription);

                    break;
            }
        }

        $xml = \Moxl\API::iqWrapper($pubsub, false, 'set');
        \Moxl\API::request($xml);
    }

}
