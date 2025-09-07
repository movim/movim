<?php

namespace Moxl\Stanza;

use App\MessageFile;
use App\MessageOmemoHeader;
use Movim\Session;

class Message
{
    public static function factory(
        string $to,
        ?string $type = null,
        ?string $id = null,
        ?string $receipts = null
    ): \DOMDocument {
        $session = Session::instance();

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $root = $dom->createElementNS('jabber:client', 'message');
        $dom->appendChild($root);
        $root->setAttribute('to', str_replace(' ', '\40', $to));

        if ($type != null) {
            $root->setAttribute('type', $type);
        }

        if ($receipts != null && in_array($receipts, ['received', 'displayed'])) {
            $root->setAttribute('id', generateUUID());
        } elseif ($id != null) {
            $root->setAttribute('id', $id);
        } else {
            $root->setAttribute('id', $session->get('id'));
        }

        return $dom;
    }

    public static function maker(
        string $to,
        ?string $content = null,
        ?string $html = null,
        ?string $type = null,
        ?string $chatstates = null,
        ?string $receipts = null,
        ?string $id = null,
        ?string $replace = null,
        ?MessageFile $file = null,
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
        $dom = Message::factory($to, $type, $id, $receipts);

        /**
         * https://xmpp.org/extensions/xep-0045.html#privatemessage
         * Resource on the to, we assume that it's a MUC PM
         */
        if (explodeJid($to)['resource'] !== null) {
            $xuser = $dom->createElementNS('http://jabber.org/protocol/muc#user', 'x');
            $dom->documentElement->appendChild($xuser);
        }

        // Thread
        if ($threadId) {
            $thread = $dom->createElement('thread', $threadId);
            $dom->documentElement->appendChild($thread);
        }

        // Message replies
        if ($replyId != false && $replyTo != false) {
            $reply = $dom->createElementNS('urn:xmpp:reply:0', 'reply');
            $reply->setAttribute('id', $replyId);
            $reply->setAttribute('to', $replyTo);
            $dom->documentElement->appendChild($reply);

            if ($replyQuotedBodyLength > 0) {
                $fallback = $dom->createElementNS('urn:xmpp:fallback:0', 'fallback');
                $fallback->setAttribute('for', 'urn:xmpp:reply:0');

                $fallbackBody = $dom->createElement('body');
                $fallbackBody->setAttribute('start', 0);
                $fallbackBody->setAttribute('end', $replyQuotedBodyLength);

                $fallback->appendChild($fallbackBody);

                $dom->documentElement->appendChild($fallback);
            }
        }

        // Chatstates
        if ($chatstates != null && $content == null) {
            $chatstate = $dom->createElementNS('http://jabber.org/protocol/chatstates', $chatstates);
            $dom->documentElement->appendChild($chatstate);
        }

        if ($content != null) {
            $chatstate = $dom->createElementNS('http://jabber.org/protocol/chatstates', 'active');
            $dom->documentElement->appendChild($chatstate);

            $body = $dom->createElement('body');


            $bodyContent = (preg_match('(>|<|&)', $content) === 1)
                ? $dom->createCDATASection($content)
                : $dom->createTextNode($content);
            $body->appendChild($bodyContent);
            $dom->documentElement->appendChild($body);
        }

        if ($replace != null) {
            $rep = $dom->createElementNS('urn:xmpp:message-correct:0', 'replace');
            $rep->setAttribute('id', $replace);
            $dom->documentElement->appendChild($rep);
        }

        if ($html != null) {
            $xhtml = $dom->createElement('html');
            $xhtml->setAttribute('xmlns', 'http://jabber.org/protocol/xhtml-im');
            $body = $dom->createElement('body');
            $body->setAttribute('xmlns', 'http://www.w3.org/1999/xhtml');

            $dom2 = new \DOMDocument('1.0', 'UTF-8');
            $dom2->loadXml('<root>' . $html . '</root>');
            $bar = $dom2->documentElement->firstChild; // we want to import the bare tree
            $body->appendChild($dom->importNode($bar, true));

            $xhtml->appendChild($body);
            $dom->documentElement->appendChild($xhtml);
        }

        if ($receipts != null) {
            if ($receipts == 'request') {
                $request = $dom->createElementNS('urn:xmpp:receipts', 'request');
            } elseif ($receipts == 'received') {
                $request = $dom->createElement('received');
                $request->setAttribute('id', $id);
                $request->setAttribute('xmlns', 'urn:xmpp:receipts');
                $dom->documentElement->appendChild($request);
            } elseif ($receipts == 'displayed') {
                $request = $dom->createElement('displayed');
                $request->setAttribute('id', $id);
                $request->setAttribute('xmlns', 'urn:xmpp:chat-markers:0');
            }

            $dom->documentElement->appendChild($request);

            if ($receipts == 'received') {
                $nostore = $dom->createElementNS('urn:xmpp:hints', 'no-store');
                $dom->documentElement->appendChild($nostore);

                $nocopy = $dom->createElementNS('urn:xmpp:hints', 'no-copy');
                $dom->documentElement->appendChild($nocopy);
            }
        }

        if (
            !in_array($receipts, ['received', 'displayed'])
            && $chatstates == 'active'
        ) {
            $markable = $dom->createElementNS('urn:xmpp:chat-markers:0', 'markable');
            $dom->documentElement->appendChild($markable);
        }

        if ($file != null) {
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
                $sreference->setAttribute('uri', $file->url);

                if (isset($file->thumbnail_url)) {
                    $thumbnail = $dom->createElement('thumbnail');
                    $thumbnail->setAttribute('xmlns', 'urn:xmpp:thumbs:1');
                    $thumbnail->setAttribute('media-type', $file->thumbnail_type);

                    if ($file->thumbnail_type == 'image/thumbhash') {
                        $thumbnail->setAttribute('uri', 'data:image/thumbhash;base64,' . $file->thumbnail_url);
                    } else {
                        $thumbnail->setAttribute('uri', $file->thumbnail_url);
                    }

                    $thumbnail->setAttribute('width', $file->thumbnail_width);
                    $thumbnail->setAttribute('height', $file->thumbnail_height);

                    $filen->appendChild($thumbnail);
                }

                $sources->appendChild($sreference);
            } else {
                $reference->setAttribute('uri', $file->url);
            }

            $dom->documentElement->appendChild($reference);

            // OOB
            $x = $dom->createElement('x');
            $x->setAttribute('xmlns', 'jabber:x:oob');
            $x->appendChild($dom->createElement('url', $file->url));

            $dom->documentElement->appendChild($x);
        }

        if ($invite != false) {
            $x = $dom->createElement('x');
            $x->setAttribute('xmlns', 'http://jabber.org/protocol/muc#user');
            $dom->documentElement->appendChild($x);

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
            $dom->documentElement->appendChild($reactionsn);

            // Force the storage of the reactions in the archive
            $store = $dom->createElement('store');
            $store->setAttribute('xmlns', 'urn:xmpp:hints');
            $dom->documentElement->appendChild($store);
        }

        if ($originId != false) {
            $origin = $dom->createElement('origin-id');
            $origin->setAttribute('xmlns', 'urn:xmpp:sid:0');
            $origin->setAttribute('id', $originId);
            $dom->documentElement->appendChild($origin);
        }

        // OMEMO
        if ($messageOMEMO) {
            $encryption = $dom->createElement('encryption');
            $encryption->setAttribute('xmlns', 'urn:xmpp:eme:0');
            $encryption->setAttribute('name', 'OMEMOE');
            $encryption->setAttribute('namespace', 'eu.siacs.conversations.axolotl');
            $dom->documentElement->appendChild($encryption);

            $messageOMEMOXML = $dom->importNode($messageOMEMO->getDom(), true);
            $dom->documentElement->appendChild($messageOMEMOXML);
        }

        \Moxl\API::sendDom($dom);
    }

    public static function message(
        string $to,
        ?string $content = null,
        ?string $html = null,
        ?string $id = null,
        ?string $replace = null,
        ?MessageFile $file = null,
        $parentId = false,
        array $reactions = [],
        $originId = false,
        $threadId = false,
        $replyId = false,
        $replyTo = false,
        $replyQuotedBodyLength = 0,
        ?MessageOmemoHeader $messageOMEMO = null
    ) {
        self::maker(
            $to,
            $content,
            $html,
            'chat',
            'active',
            'request',
            $id,
            $replace,
            $file,
            false,
            $parentId,
            $reactions,
            $originId,
            $threadId,
            $replyId,
            $replyTo,
            $replyQuotedBodyLength,
            $messageOMEMO
        );
    }

    public static function simpleMessage(
        string $to,
        ?string $content = null,
        ?string $html = null,
        ?string $id = null,
        ?string $replace = null,
        ?MessageFile $file = null,
        $parentId = false,
        array $reactions = [],
        $originId = false,
        $threadId = false,
        $replyId = false,
        $replyTo = false,
        $replyQuotedBodyLength = 0,
        ?MessageOmemoHeader $messageOMEMO = null
    ) {
        self::maker(
            $to,
            $content,
            $html,
            'chat',
            false,
            false,
            $id,
            $replace,
            $file,
            false,
            $parentId,
            $reactions,
            $originId,
            $threadId,
            $replyId,
            $replyTo,
            $replyQuotedBodyLength,
            $messageOMEMO
        );
    }

    public static function received($to, $id, $type = 'chat')
    {
        self::maker($to, type: $type, receipts: 'received', id: $id);
    }

    public static function displayed($to, $id, $type = 'chat')
    {
        self::maker($to, type: $type, receipts: 'displayed', id: $id);
    }

    public static function invite($to, $id, $invite)
    {
        self::maker($to, id: $id, invite: $invite);
    }

    public static function active($to)
    {
        self::maker($to, type: 'chat', chatstates: 'active');
    }

    public static function inactive($to)
    {
        self::maker($to, type: 'chat', chatstates: 'inactive');
    }

    public static function composing($to)
    {
        self::maker($to, type: 'chat', chatstates: 'composing');
    }

    public static function paused($to)
    {
        self::maker($to, type: 'chat', chatstates: 'paused');
    }

    public static function retract(string $to, string $id, string $type)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $root = $dom->createElementNS('jabber:client', 'message');
        $dom->appendChild($root);
        $root->setAttribute('to', str_replace(' ', '\40', $to));
        $root->setAttribute('type', $type);
        $root->setAttribute('id', generateUUID());

        $retract = $dom->createElement('retract');
        $retract->setAttribute('xmlns', 'urn:xmpp:message-retract:1');
        $retract->setAttribute('id', $id);
        $root->appendChild($retract);

        // Hints
        $store = $dom->createElement('store');
        $store->setAttribute('xmlns', 'urn:xmpp:hints');
        $root->appendChild($store);

        // Fallback
        $fallback = $dom->createElementNS('urn:xmpp:fallback:0', 'fallback');
        $fallback->setAttribute('for', 'urn:xmpp:message-retract:1');
        $root->appendChild($fallback);

        $body = $dom->createElement('body');
        $bodyContent = $dom->createTextNode(__('message.retract_body'));
        $body->appendChild($bodyContent);
        $root->appendChild($body);

        \Moxl\API::sendDom($dom);
    }

    public static function moderate(string $to, string $stanzaId)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');

        $moderate = $dom->createElement('moderate');
        $moderate->setAttribute('xmlns', 'urn:xmpp:message-moderate:1');
        $moderate->setAttribute('id', $stanzaId);

        $reason = $dom->createElement('reason');
        $reasonContent = $dom->createTextNode(__('message.moderate_body'));
        $reason->appendChild($reasonContent);
        $moderate->appendChild($reason);

        $retract = $dom->createElement('retract');
        $retract->setAttribute('xmlns', 'urn:xmpp:message-retract:1');
        $moderate->appendChild($retract);

        \Moxl\API::request(\Moxl\API::iqWrapper($moderate, $to, 'set'));
    }
}
