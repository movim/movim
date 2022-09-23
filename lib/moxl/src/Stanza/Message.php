<?php

namespace Moxl\Stanza;

use App\MessageOmemoHeader;
use Movim\Session;

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
        $parentId = false,
        array $reactions = [],
        $originId = false,
        $threadId = false,
        $replyId = false,
        $replyTo = false,
        $replyQuotedBodyLength = 0,
        ?MessageOmemoHeader $messageOMEMO = null
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

        /**
         * https://xmpp.org/extensions/xep-0045.html#privatemessage
         * Resource on the to, we assume that it's a MUC PM
         */
        if (explodeJid($to)['resource'] !== null) {
            $xuser = $dom->createElementNS('http://jabber.org/protocol/muc#user', 'x');
            $root->appendChild($xuser);
        }

        // Thread
        if ($threadId) {
            $thread = $dom->createElement('thread', $threadId);
            $root->appendChild($thread);
        }

        // Message replies
        if ($replyId != false && $replyTo != false) {
            $reply = $dom->createElementNS('urn:xmpp:reply:0', 'reply');
            $reply->setAttribute('id', $replyId);
            $reply->setAttribute('to', $replyTo);
            $root->appendChild($reply);

            if ($replyQuotedBodyLength > 0) {
                $fallback = $dom->createElementNS('urn:xmpp:feature-fallback:0', 'fallback');
                $fallback->setAttribute('for', 'urn:xmpp:reply:0');

                $fallbackBody = $dom->createElement('body');
                $fallbackBody->setAttribute('start', 0);
                $fallbackBody->setAttribute('end', $replyQuotedBodyLength);

                $fallback->appendChild($fallbackBody);

                $root->appendChild($fallback);
            }
        }

        // Chatstates
        if ($chatstates != false && $content == false) {
            $chatstate = $dom->createElementNS('http://jabber.org/protocol/chatstates', $chatstates);
            $root->appendChild($chatstate);
        }

        if ($content != false) {
            $chatstate = $dom->createElementNS('http://jabber.org/protocol/chatstates', 'active');
            $root->appendChild($chatstate);

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

            if ($receipts == 'received') {
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
            $reference = $dom->createElement('reference');
            $reference->setAttribute('xmlns', 'urn:xmpp:reference:0');
            $reference->setAttribute('type', 'data');

            // SIMS
            if (isset($file->type) && isset($file->name) && isset($file->size)) {
                $media = $dom->createElement('media-sharing');
                $media->setAttribute('xmlns', 'urn:xmpp:sims:1');
                $reference->appendChild($media);

                $filen = $dom->createElement('file');
                $filen->setAttribute('xmlns', 'urn:xmpp:jingle:apps:file-transfer:4');
                $media->appendChild($filen);

                // xmpp/uri is an internal placeholder
                if ($file->type != 'xmpp/uri') {
                    $filen->appendChild($dom->createElement('media-type', $file->type));
                }

                if (!empty((string)$file->name)) {
                    $filen->appendChild($dom->createElement('name', $file->name));
                }

                if ((int)$file->size > 0) {
                    $filen->appendChild($dom->createElement('size', $file->size));
                }

                $sources = $dom->createElement('sources');
                $media->appendChild($sources);

                $sreference = $dom->createElement('reference');
                $sreference->setAttribute('xmlns', 'urn:xmpp:reference:0');
                $sreference->setAttribute('type', 'data');
                $sreference->setAttribute('uri', $file->uri);

                if (isset($file->thumbnail->uri)) {
                    $thumbnail = $dom->createElement('thumbnail');
                    $thumbnail->setAttribute('xmlns', 'urn:xmpp:thumbs:1');
                    $thumbnail->setAttribute('media-type', $file->thumbnail->type);
                    $thumbnail->setAttribute('uri', $file->thumbnail->uri);
                    $thumbnail->setAttribute('width', $file->thumbnail->width);
                    $thumbnail->setAttribute('height', $file->thumbnail->height);

                    $filen->appendChild($thumbnail);
                }

                $sources->appendChild($sreference);
            } else {
                $reference->setAttribute('uri', $file->uri);
            }

            $root->appendChild($reference);

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

        if ($parentId != false) {
            $reactionsn = $dom->createElement('reactions');
            $reactionsn->setAttribute('xmlns', 'urn:xmpp:reactions:0');
            $reactionsn->setAttribute('id', $parentId);

            foreach ($reactions as $emoji) {
                $reaction = $dom->createElement('reaction', $emoji);
                $reactionsn->appendChild($reaction);
            }
            $root->appendChild($reactionsn);

            // Force the storage of the reactions in the archive
            $store = $dom->createElement('store');
            $store->setAttribute('xmlns', 'urn:xmpp:hints');
            $root->appendChild($store);
        }

        if ($originId != false) {
            $origin = $dom->createElement('origin-id');
            $origin->setAttribute('xmlns', 'urn:xmpp:sid:0');
            $origin->setAttribute('id', $originId);
            $root->appendChild($origin);
        }

        // OMEMO
        if ($messageOMEMO) {
            $encryption = $dom->createElement('encryption');
            $encryption->setAttribute('xmlns', 'urn:xmpp:eme:0');
            $encryption->setAttribute('name', 'OMEMOE');
            $encryption->setAttribute('namespace', 'eu.siacs.conversations.axolotl');
            $root->appendChild($encryption);

            $messageOMEMOXML = $dom->importNode($messageOMEMO->getDom(), true);
            $root->appendChild($messageOMEMOXML);
        }

        \Moxl\API::request($dom->saveXML($dom->documentElement));
    }

    public static function message($to, $content = false, $html = false, $id = false,
        $replace = false, $file = false, $parentId = false, array $reactions = [],
        $originId = false, $threadId = false, $replyId = false, $replyTo = false,
        $replyQuotedBodyLength = 0, ?MessageOmemoHeader $messageOMEMO = null)
    {
        self::maker($to, $content, $html, 'chat', 'active', 'request', $id, $replace,
            $file, false, $parentId, $reactions, $originId, $threadId, $replyId,
            $replyTo, $replyQuotedBodyLength, $messageOMEMO);
    }

    public static function simpleMessage($to, $content = false, $html = false, $id = false,
    $replace = false, $file = false, $parentId = false, array $reactions = [],
    $originId = false, $threadId = false, $replyId = false, $replyTo = false,
    $replyQuotedBodyLength = 0, ?MessageOmemoHeader $messageOMEMO = null)
    {
        self::maker($to, $content, $html, 'chat', false, false, $id, $replace,
            $file, false, $parentId, $reactions, $originId, $threadId, $replyId,
            $replyTo, $replyQuotedBodyLength, $messageOMEMO);
    }

    public static function receipt($to, $id)
    {
        self::maker($to, false, false, 'chat', false, 'received', $id);
    }

    public static function displayed($to, $id, $type = 'chat')
    {
        self::maker($to, false, false, $type, false, 'displayed', $id);
    }

    public static function invite($to, $id, $invite)
    {
        self::maker($to, false, false, false, false, false, $id, false, false, $invite);
    }

    public static function active($to)
    {
        self::maker($to, false, false, 'chat', 'active');
    }

    public static function inactive($to)
    {
        self::maker($to, false, false, 'chat', 'inactive');
    }

    public static function composing($to)
    {
        self::maker($to, false, false, 'chat', 'composing');
    }

    public static function paused($to)
    {
        self::maker($to, false, false, 'chat', 'paused');
    }

    public static function retract(string $to, string $originId)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $root = $dom->createElementNS('jabber:client', 'message');
        $dom->appendChild($root);
        $root->setAttribute('to', str_replace(' ', '\40', $to));
        $root->setAttribute('type', 'chat');
        $root->setAttribute('id', generateUUID());

        $apply = $dom->createElement('apply-to');
        $apply->setAttribute('xmlns', 'urn:xmpp:fasten:0');
        $apply->setAttribute('id', $originId);
        $root->appendChild($apply);

        $retract = $dom->createElement('retract');
        $retract->setAttribute('xmlns', 'urn:xmpp:message-retract:0');
        $apply->appendChild($retract);

        // Hints
        $store = $dom->createElement('store');
        $store->setAttribute('xmlns', 'urn:xmpp:hints');
        $root->appendChild($store);

        // Fallback
        $fallback = $dom->createElementNS('urn:xmpp:feature-fallback:0', 'fallback');
        $root->appendChild($fallback);

        $body = $dom->createElement('body');
        $bodyContent = $dom->createTextNode(__('message.retract_body'));
        $body->appendChild($bodyContent);
        $root->appendChild($body);

        \Moxl\API::request($dom->saveXML($dom->documentElement));
    }
}
