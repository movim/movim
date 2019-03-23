<?php

namespace Moxl\Stanza;

use Movim\Session;
use Moxl\Utils;

class Message
{
    public static function maker(
        $to,
        $content = false,
        $html = false,
        $type = false,
        $chatstates = false,
        $receipts = false,
        $id = false,
        $replace = false,
        $file = false,
        $invite = false,
        $attachId = false
    ) {
        $session = Session::start();

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $root = $dom->createElementNS('jabber:client', 'message');
        $dom->appendChild($root);
        $root->setAttribute('to', str_replace(' ', '\40', $to));

        if ($type != false) {
            $root->setAttribute('type', $type);
        }

        if (in_array($receipts, ['received', 'displayed'])) {
            $root->setAttribute('id', generateUUID());
        } elseif ($id != false) {
            $root->setAttribute('id', $id);
        } else {
            $root->setAttribute('id', $session->get('id'));
        }

        if ($content != false) {
            $body = $dom->createElement('body');
            $bodyContent = $dom->createTextNode($content);
            $body->appendChild($bodyContent);
            $root->appendChild($body);
        }

        if ($replace != false) {
            $rep = $dom->createElementNS('urn:xmpp:message-correct:0', 'replace');
            $rep->setAttribute('id', $replace);
            $root->appendChild($rep);
        }

        if ($html != false) {
            $xhtml = $dom->createElementNS('http://jabber.org/protocol/xhtml-im', 'html');
            $body = $dom->createElementNS('http://www.w3.org/1999/xhtml', 'body');

            $dom2 = new \DOMDocument('1.0', 'UTF-8');
            $dom2->loadXml('<root>'.$html.'</root>');
            $bar = $dom2->documentElement->firstChild; // we want to import the bar tree
            $body->appendChild($dom->importNode($bar, true));

            $xhtml->appendChild($body);
            $root->appendChild($xhtml);
        }

        if ($chatstates != false) {
            $chatstate = $dom->createElementNS('http://jabber.org/protocol/chatstates', $chatstates);
            $root->appendChild($chatstate);
        }

        if ($receipts != false) {
            if ($receipts == 'request') {
                $request = $dom->createElementNS('urn:xmpp:receipts', 'request');
            } elseif ($receipts == 'received') {
                $request = $dom->createElement('received');
                $request->setAttribute('id', $id);
                $request->setAttribute('xmlns', 'urn:xmpp:receipts');
                $root->appendChild($request);
            } elseif ($receipts == 'displayed') {
                $request = $dom->createElement('displayed');
                $request->setAttribute('id', $id);
                $request->setAttribute('xmlns', 'urn:xmpp:chat-markers:0');
            }

            $root->appendChild($request);

            if ($receipts != 'request') {
                $nostore = $dom->createElementNS('urn:xmpp:hints', 'no-store');
                $root->appendChild($nostore);

                $nocopy = $dom->createElementNS('urn:xmpp:hints', 'no-copy');
                $root->appendChild($nocopy);
            }
        }

        if (!in_array($receipts, ['received', 'displayed'])
        && $chatstates == 'active') {
            $markable = $dom->createElementNS('urn:xmpp:chat-markers:0', 'markable');
            $root->appendChild($markable);
        }

        if ($file != false) {
            // SIMS
            $reference = $dom->createElement('reference');
            $reference->setAttribute('xmlns', 'urn:xmpp:reference:0');
            $reference->setAttribute('type', 'data');
            $root->appendChild($reference);

            $media = $dom->createElement('media-sharing');
            $media->setAttribute('xmlns', 'urn:xmpp:sims:1');
            $reference->appendChild($media);

            $filen = $dom->createElement('file');
            $filen->setAttribute('xmlns', 'urn:xmpp:jingle:apps:file-transfer:4');
            $media->appendChild($filen);

            $filen->appendChild($dom->createElement('media-type', $file->type));
            $filen->appendChild($dom->createElement('name', $file->name));
            $filen->appendChild($dom->createElement('size', $file->size));

            $sources = $dom->createElement('sources');
            $media->appendChild($sources);

            $reference = $dom->createElement('reference');
            $reference->setAttribute('xmlns', 'urn:xmpp:reference:0');
            $reference->setAttribute('type', 'data');
            $reference->setAttribute('uri', $file->uri);

            $sources->appendChild($reference);

            // OOB
            $x = $dom->createElement('x');
            $x->setAttribute('xmlns', 'jabber:x:oob');
            $x->appendChild($dom->createElement('url', $file->uri));

            $root->appendChild($x);
        }

        if ($invite != false) {
            $x = $dom->createElement('x');
            $x->setAttribute('xmlns', 'http://jabber.org/protocol/muc#user');
            $root->appendChild($x);

            $xinvite = $dom->createElement('invite');
            $xinvite->setAttribute('to', $invite);
            $x->appendChild($xinvite);
        }

        if ($attachId != false) {
            $attach = $dom->createElement('attach-to');
            $attach->setAttribute('xmlns', 'urn:xmpp:message-attaching:1');
            $attach->setAttribute('id', $attachId);
            $root->appendChild($attach);
        }

        \Moxl\API::request($dom->saveXML($dom->documentElement));
    }

    public static function message($to, $content, $html = false, $id = false, $replace = false, $file = false, $attachId = false)
    {
        self::maker($to, $content, $html, 'chat', 'active', 'request', $id, $replace, $file, false, $attachId);
    }

    public static function composing($to)
    {
        self::maker($to, false, false, 'chat', 'composing');
    }

    public static function paused($to)
    {
        self::maker($to, false, false, 'chat', 'paused');
    }

    public static function receipt($to, $id)
    {
        self::maker($to, false, false, 'chat', false, 'received', $id);
    }

    public static function displayed($to, $id)
    {
        self::maker($to, false, false, 'chat', false, 'displayed', $id);
    }

    public static function invite($to, $id, $invite)
    {
        self::maker($to, false, false, false, false, false, $id, false, false, $invite);
    }
}
