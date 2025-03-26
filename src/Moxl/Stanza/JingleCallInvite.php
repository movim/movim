<?php

namespace Moxl\Stanza;

class JingleCallInvite
{
    public static function invite(string $to, string $id, string $room, bool $video = false)
    {
        $dom = Message::factory($to, 'groupchat');

        $invite = $dom->createElementNS('urn:xmpp:call-invites:0', 'invite');
        $invite->setAttribute('id', $id);

        if ($video) {
            $invite->setAttribute('video', 'true');
        }

        $muji = $dom->createElement('muji');
        $muji->setAttribute('xmlns', 'urn:xmpp:jingle:muji:0');
        $muji->setAttribute('room', $room);

        $invite->appendChild($muji);

        $dom->documentElement->appendChild($invite);

        $store = $dom->createElement('store');
        $store->setAttribute('xmlns', 'urn:xmpp:hints');
        $dom->documentElement->appendChild($store);

        \Moxl\API::sendDom($dom);
    }

    public static function retract(string $to, string $id)
    {
        $dom = Message::factory($to, 'groupchat');

        $retract = $dom->createElementNS('urn:xmpp:call-invites:0', 'retract');
        $retract->setAttribute('id', $id);

        $dom->documentElement->appendChild($retract);

        $store = $dom->createElement('store');
        $store->setAttribute('xmlns', 'urn:xmpp:hints');
        $dom->documentElement->appendChild($store);

        \Moxl\API::sendDom($dom);
    }

    public static function accept(string $to, string $id)
    {
        $dom = Message::factory($to, 'groupchat');

        $accept = $dom->createElementNS('urn:xmpp:call-invites:0', 'accept');
        $accept->setAttribute('id', $id);

        $dom->documentElement->appendChild($accept);

        $store = $dom->createElement('store');
        $store->setAttribute('xmlns', 'urn:xmpp:hints');
        $dom->documentElement->appendChild($store);

        \Moxl\API::sendDom($dom);
    }

    public static function reject(string $to, string $id)
    {
        $dom = Message::factory($to, 'groupchat');

        $reject = $dom->createElementNS('urn:xmpp:call-invites:0', 'reject');
        $reject->setAttribute('id', $id);

        $dom->documentElement->appendChild($reject);

        $store = $dom->createElement('store');
        $store->setAttribute('xmlns', 'urn:xmpp:hints');
        $dom->documentElement->appendChild($store);

        \Moxl\API::sendDom($dom);
    }

    public static function left(string $to, string $id)
    {
        $dom = Message::factory($to, 'groupchat');

        $left = $dom->createElementNS('urn:xmpp:call-invites:0', 'left');
        $left->setAttribute('id', $id);

        $dom->documentElement->appendChild($left);

        $store = $dom->createElement('store');
        $store->setAttribute('xmlns', 'urn:xmpp:hints');
        $dom->documentElement->appendChild($store);

        \Moxl\API::sendDom($dom);
    }
}
