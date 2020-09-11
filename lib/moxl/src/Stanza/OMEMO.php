<?php

namespace Moxl\Stanza;

use Movim\Session;

class OMEMO
{
    public static function setDeviceList($ids)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElement('pubsub');
        $pubsub->setAttribute('xmlns', 'http://jabber.org/protocol/pubsub');

        $publish = $dom->createElement('publish');
        $publish->setAttribute('node', 'eu.siacs.conversations.axolotl.devicelist');
        $pubsub->appendChild($publish);

        $item = $dom->createElement('item');
        $publish->appendChild($item);

        $list = $dom->createElement('list');
        $list->setAttribute('xmlns', 'eu.siacs.conversations.axolotl');
        $item->appendChild($list);

        foreach ($ids as $id) {
            $device = $dom->createElement('device');
            $device->setAttribute('id', $id);
            $list->appendChild($device);
        }

        $xml = \Moxl\API::iqWrapper($pubsub, false, 'set');
        \Moxl\API::request($xml);
    }

    public static function getBundle($to, $id)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElement('pubsub');
        $pubsub->setAttribute('xmlns', 'http://jabber.org/protocol/pubsub');

        $items = $dom->createElement('items');
        $items->setAttribute('node', 'eu.siacs.conversations.axolotl.bundles:'.$id);
        $pubsub->appendChild($items);

        $xml = \Moxl\API::iqWrapper($pubsub, $to, 'get');
        \Moxl\API::request($xml);
    }

    public static function announceBundle(
        $id,
        $signedPreKeyPublic,
        $signedPreKeySignature,
        $identityKey,
        $preKeys
    ) {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElement('pubsub');
        $pubsub->setAttribute('xmlns', 'http://jabber.org/protocol/pubsub');

        $publish = $dom->createElement('publish');
        $publish->setAttribute('node', 'eu.siacs.conversations.axolotl.bundles:'.$id);
        $pubsub->appendChild($publish);

        $item = $dom->createElement('item');
        $item->setAttribute('id', 'current');
        $publish->appendChild($item);

        $bundle = $dom->createElement('bundle');
        $bundle->setAttribute('xmlns', 'eu.siacs.conversations.axolotl');
        $item->appendChild($bundle);

        $spkp = $dom->createElement('signedPreKeyPublic', $signedPreKeyPublic);
        $spkp->setAttribute('signedPreKeyId', 1);
        $bundle->appendChild($spkp);

        $spks = $dom->createElement('signedPreKeySignature', $signedPreKeySignature);
        $bundle->appendChild($spks);

        $ik = $dom->createElement('identityKey', $identityKey);
        $bundle->appendChild($ik);

        $pks = $dom->createElement('prekeys');
        $bundle->appendChild($pks);

        $i = 1;

        foreach ($preKeys as $i => $key) {
            $pkp = $dom->createElement('preKeyPublic', $key);
            $pkp->setAttribute('preKeyId', $i);
            $pks->appendChild($pkp);
            $i++;
        }

        $xml = \Moxl\API::iqWrapper($pubsub, false, 'set');
        \Moxl\API::request($xml);
    }

    public static function message(
        $to,
        $sid,
        $key,
        $rid,
        $iv,
        $payload,
        $isprekey
    ) {
        $session = Session::start();

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $root = $dom->createElementNS('jabber:client', 'message');
        $dom->appendChild($root);
        $root->setAttribute('type', 'chat');
        $root->setAttribute('to', str_replace(' ', '\40', $to));
        $root->setAttribute('id', $session->get('id'));

        $encrypted = $dom->createElement('encrypted');
        $encrypted->setAttribute('xmlns', 'eu.siacs.conversations.axolotl');
        $root->appendChild($encrypted);

        $header = $dom->createElement('header');
        $header->setAttribute('sid', $sid);
        $encrypted->appendChild($header);

        $key = $dom->createElement('key', $key);

        if ($isprekey) {
            $key->setAttribute('prekey', 'true');
        }

        $key->setAttribute('rid', $rid);
        $header->appendChild($key);

        $iv = $dom->createElement('iv', $iv);
        $header->appendChild($iv);

        $payload = $dom->createElement('payload', $payload);
        $encrypted->appendChild($payload);

        \Moxl\API::request($dom->saveXML($dom->documentElement));
    }
}
