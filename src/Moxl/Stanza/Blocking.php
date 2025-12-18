<?php

namespace Moxl\Stanza;

class Blocking
{
    public static function request()
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $blocklist = $dom->createElementNS('urn:xmpp:blocking', 'blocklist');

        return $blocklist;
    }

    public static function block(string $jid)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $block = $dom->createElementNS('urn:xmpp:blocking', 'block');

        $item = $dom->createElement('item');
        $item->setAttribute('jid', $jid);
        $block->appendChild($item);

        return $block;
    }

    public static function unblock(string $jid)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $unblock = $dom->createElementNS('urn:xmpp:blocking', 'unblock');

        $item = $dom->createElement('item');
        $item->setAttribute('jid', $jid);
        $unblock->appendChild($item);

        return $unblock;
    }
}
