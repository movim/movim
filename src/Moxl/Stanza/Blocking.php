<?php

namespace Moxl\Stanza;

class Blocking
{
    public static function request()
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $blocklist = $dom->createElementNS('urn:xmpp:blocking', 'blocklist');

        \Moxl\API::request(\Moxl\API::iqWrapper($blocklist, null, 'get'));
    }

    public static function block(string $jid)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $block = $dom->createElementNS('urn:xmpp:blocking', 'block');

        $item = $dom->createElement('item');
        $item->setAttribute('jid', $jid);
        $block->appendChild($item);

        \Moxl\API::request(\Moxl\API::iqWrapper($block, null, 'set'));
    }

    public static function unblock(string $jid)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $unblock = $dom->createElementNS('urn:xmpp:blocking', 'unblock');

        $item = $dom->createElement('item');
        $item->setAttribute('jid', $jid);
        $unblock->appendChild($item);

        \Moxl\API::request(\Moxl\API::iqWrapper($unblock, null, 'set'));
    }
}
