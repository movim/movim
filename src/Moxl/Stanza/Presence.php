<?php

namespace Moxl\Stanza;

use App\User;
use DOMElement;

class Presence
{
    /*
     * The presence builder
     */
    public static function maker(
        ?User $me = null,
        ?string $to = null,
        ?string $status = null,
        ?string $show = null,
        int $priority = 0,
        ?string $type = null,
        bool $muc = false,
        bool $mam = false,
        bool $mujiPreparing = false,
        ?DOMElement $muji = null,
        $last = 0
    ) {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $root = $dom->createElementNS('jabber:client', 'presence');
        $dom->appendChild($root);

        if ($me && $me->session) {
            $root->setAttribute('from', $me->id . '/' . $me->session->resource);
        }

        $root->setAttribute('id', linker($me->session->id)->session->get('id'));

        if ($to) {
            $root->setAttribute('to', $to);
        }

        if ($type) {
            $root->setAttribute('type', $type);
        }

        if ($status) {
            $status = $dom->createElement('status', $status);
            $root->appendChild($status);
        }

        if ($show) {
            $show = $dom->createElement('show', $show);
            $root->appendChild($show);
        }

        if ($priority != 0) {
            $priority = $dom->createElement('priority', $priority);
            $root->appendChild($priority);
        }

        if ($muji != null) {
            $root->append($dom->importNode($muji, true));
        }

        // https://xmpp.org/extensions/xep-0319.html#last-interact
        if ($last > 0) {
            $timestamp = time() - $last;
            $idle = $dom->createElementNS('urn:xmpp:idle:1', 'idle');
            $idle->setAttribute('since', gmdate('c', $timestamp));
            $root->appendChild($idle);
        }

        if ($mujiPreparing) {
            $muji = $dom->createElementNS('urn:xmpp:jingle:muji:0', 'muji');
            $muji->appendChild($dom->createElement('preparing'));
            $root->appendChild($muji);
        }

        if ($muc) {
            $x = $dom->createElementNS('http://jabber.org/protocol/muc', 'x');

            if ($mam) {
                $history = $dom->createElement('history');
                $history->setAttribute('maxchars', 0);
                $x->appendChild($history);
            }

            $root->appendChild($x);
        }

        $c = $dom->createElementNS('urn:xmpp:caps', 'c');
        $hash = $dom->createElement('hash', \Moxl\Utils::getOwnCapabilityHash());
        $hash->setAttribute('xmlns', 'urn:xmpp:hashes:2');
        $hash->setAttribute('algo', \Moxl\Utils::CAPABILITY_HASH_ALGORITHM);

        $c->appendChild($hash);

        $root->appendChild($c);

        $c = $dom->createElementNS('http://jabber.org/protocol/caps', 'c');
        $c->setAttribute('hash', 'sha-1');
        $c->setAttribute('node', 'https://movim.eu/');
        $c->setAttribute('ver', \Moxl\Utils::generateCaps());
        $root->appendChild($c);

        return $dom;
    }
}
