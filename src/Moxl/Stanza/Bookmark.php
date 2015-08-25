<?php

namespace Moxl\Stanza;

class Bookmark {
    static function get()
    {
        $xml = '
        <pubsub xmlns="http://jabber.org/protocol/pubsub">
            <items node="storage:bookmarks"/>
        </pubsub>';

        $xml = \Moxl\API::iqWrapper($xml, false, 'get');
        \Moxl\API::request($xml);
    }

    static function set($arr)
    {
        $xml = '';

        /*
        $xml = '
        <pubsub xmlns="http://jabber.org/protocol/pubsub">
            <publish node="storage:bookmarks">
                <item id="current">
                    <storage xmlns="storage:bookmarks">
                        '.$xml.'
                    </storage>
                </item>
            </publish>
            </pubsub>';*/
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
                    /*$xml .= '
                        <conference name="'.$elt['name'].'"
                                    autojoin="'.$elt['autojoin'].'"
                                    jid="'.$elt['jid'].'">
                            <nick>'.$elt['nick'].'</nick>
                            </conference>';*/
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
                    /*
                    $xml .= '
                        <subscription
                            xmlns="urn:xmpp:pubsub:subscription:0"
                            server="'.$elt['server'].'"
                            node="'.$elt['node'].'"
                            subid="'.$elt['subid'].'">
                            <title>'.$elt['title'].'</title>
                            </subscription>';*/
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
