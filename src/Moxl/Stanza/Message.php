<?php

namespace Moxl\Stanza;

class Message {
    static function maker(
        $to,
        $content = false,
        $html = false,
        $type = 'chat',
        $chatstates = false,
        $receipts = false,
        $id = false,
        $replace = false)
    {
        $session = \Sessionx::start();

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $root = $dom->createElementNS('jabber:client', 'message');
        $dom->appendChild($root);
        $root->setAttribute('to', str_replace(' ', '\40', $to));
        $root->setAttribute('type', $type);

        if($id != false) {
            $root->setAttribute('id', $id);
        } else {
            $root->setAttribute('id', $session->id);
        }

        if($content != false) {
            $body = $dom->createElement('body', $content);
            $root->appendChild($body);
        }

        if($replace != false) {
            $rep = $dom->createElementNS('urn:xmpp:message-correct:0', 'replace');
            $rep->setAttribute('id', $replace);
            $root->appendChild($rep);
        }

        if($html != false) {
            $xhtml = $dom->createElementNS('http://jabber.org/protocol/xhtml-im', 'html');
            $body = $dom->createElement('http://www.w3.org/1999/xhtml', 'body', $html);

            $xhtml->appendChild($body);
            $root->appendChild($xhtml);
        }

        if($chatstates != false) {
            $chatstate = $dom->createElementNS('http://jabber.org/protocol/chatstates', $chatstates);
            $root->appendChild($chatstate);
        }

        if($receipts != false) {
            if($receipts == 'request') {
                $request = $dom->createElement('request');
            } else {
                $request = $dom->createElement('received');
                $request->setAttribute('id', $receipts);
            }
            $request->setAttribute('xmlns', 'urn:xmpp:receipts');
            $root->appendChild($request);
        }

        \Moxl\API::request($dom->saveXML($dom->documentElement));
    }

    static function message($to, $content, $html = false, $id = false, $replace = false)
    {
        self::maker($to, $content, $html, 'chat', 'active', 'request', $id, $replace);
    }
    /*
    static function encrypted($to, $content)
    {
        $session = \Sessionx::start();
        $xml = '
            <message xmlns="jabber:client" to="'.str_replace(' ', '\40', $to).'" type="chat" id="'.$session->id.'">
                <body>You receive an encrypted message</body>
                <x xmlns="jabber:x:encrypted">
                    '.$content.'
                </x>
                <active xmlns="http://jabber.org/protocol/chatstates"/>
                <request xmlns="urn:xmpp:receipts"/>
            </message>';
        \Moxl\API::request($xml);
    }
    */
    static function composing($to)
    {
        self::maker($to, false, false, 'chat', 'composing');
    }

    static function paused($to)
    {
        self::maker($to, false, false, 'chat', 'paused');
    }

    static function receipt($to, $id)
    {
        self::maker($to, false, false, 'chat', false, $id);
    }
}
