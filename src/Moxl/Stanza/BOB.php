<?php

namespace Moxl\Stanza;

class BOB
{
    public static function request(string $hash, string $algorythm)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $data = $dom->createElementNS('urn:xmpp:bob', 'data');
        $data->setAttribute('cid', $algorythm . '+' . $hash . '@bob.xmpp.org');

        return $data;
    }

    public static function answer($id, $cid, $type, $base64)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $data = $dom->createElementNS('urn:xmpp:bob', 'data', $base64);
        $data->setAttribute('cid', $cid);
        $data->setAttribute('type', $type);
        $data->setAttribute('max-age', '86400');

        return $data;
    }
}
