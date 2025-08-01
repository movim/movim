<?php

namespace Moxl;

use App\Post;

class Utils
{
    public static function getSupportedServices()
    {
        return [
            'urn:xmpp:microblog:0',
            Post::MICROBLOG_NODE . '+notify',
            Post::STORIES_NODE . '+notify',
            'urn:xmpp:bookmarks:0+notify',
            'urn:xmpp:bookmarks:1+notify',
            'urn:xmpp:pubsub:subscription',
            'urn:xmpp:pubsub:subscription+notify',

            'eu.siacs.conversations.axolotl.devicelist',
            'eu.siacs.conversations.axolotl.devicelist+notify',

            'urn:xmpp:sid:0',

            'urn:xmpp:attention:0',
            'urn:xmpp:vcard4',
            'urn:xmpp:vcard4+notify',
            'urn:xmpp:avatar:data',
            'urn:xmpp:avatar:metadata',
            'urn:xmpp:avatar:metadata+notify',

            'urn:xmpp:movim-banner:0',
            'urn:xmpp:movim-banner:0+notify',

            'urn:xmpp:receipts',
            'urn:xmpp:carbons:2',
            'jabber:iq:version',
            'jabber:iq:last',
            'vcard-temp',
            'jabber:x:data',
            'urn:xmpp:ping',
            'urn:xmpp:message-correct:0',
            'urn:xmpp:message-retract:1',
            'urn:xmpp:message-moderate:1',
            'urn:xmpp:bob',
            'urn:xmpp:chat-markers:0',
            'urn:xmpp:reference:0',
            'urn:xmpp:message-attaching:1',
            'urn:xmpp:reactions:0',

            // Jingle
            'http://jabber.org/protocol/jingle',
            'urn:xmpp:jingle:1',
            'urn:xmpp:jingle:apps:rtp:1',
            'urn:xmpp:jingle:apps:rtp:audio',
            'urn:xmpp:jingle:apps:rtp:video',
            'urn:xmpp:jingle:apps:rtp:rtp-hdrext:0',
            'urn:ietf:rfc:3264',
            'urn:ietf:rfc:5888',
            'urn:xmpp:jingle:apps:dtls:0',
            'urn:ietf:rfc:5576',
            'urn:xmpp:jingle:transports:ice-udp:0',
            'urn:xmpp:jingle:transports:ice-udp:1',
            'urn:xmpp:jingle:apps:rtp:rtcp-fb:0',
            'urn:xmpp:jingle-message:0',
            'urn:xmpp:jingle:muji:0',
            'urn:xmpp:call-invites:0',

            'http://jabber.org/protocol/muc',
            'http://jabber.org/protocol/nick',
            'http://jabber.org/protocol/nick+notify',
            'http://jabber.org/protocol/xhtml-im',
            'http://jabber.org/protocol/chatstates',
            'http://jabber.org/protocol/caps',
            'http://jabber.org/protocol/disco#info',
            'http://jabber.org/protocol/disco#items',
            //'http://jabber.org/protocol/activity',
            'http://jabber.org/protocol/geoloc',
            'http://jabber.org/protocol/geoloc+notify',
            'http://jabber.org/protocol/pubsub',
            //'http://jabber.org/protocol/tune',
            //'http://jabber.org/protocol/tune+notify';
        ];
    }

    public static function injectConfigInX(\DOMNode $x, array $inputs)
    {
        foreach ($inputs as $key => $value) {
            $field = $x->ownerDocument->createElement('field');
            $x->appendChild($field);

            if ($key == 'FORM_TYPE') {
                $field->setAttribute('type', 'hidden');
            }

            $val = $x->ownerDocument->createElement('value');
            $field->appendChild($val);

            if (is_bool($value)) {
                $val->nodeValue = ($value) ? 'true' : 'false';
            } else {
                if ($value === 'true') {
                    $val->nodeValue = 'true';
                }

                if ($value === 'false') {
                    $val->nodeValue = 'false';
                } elseif (empty($val->nodeValue)) {
                    $val->appendChild($x->ownerDocument->createTextNode(trim($value)));
                }
            }

            $field->setAttribute('var', trim($key));
        }
    }

    public static function generateCaps()
    {
        $s = '';
        $s .= 'client/web//Movim<';

        $support = Utils::getSupportedServices();

        asort($support);
        foreach ($support as $sup) {
            $s = $s . $sup . '<';
        }

        return base64_encode(sha1(mb_convert_encoding($s, 'UTF-8', 'ISO-8859-1'), true));
    }

    // XEP-0106: JID Escaping
    public static function escapeJidLocalpart($s)
    {
        $result = '';

        $chars = [' ', '"', '&', '\'', '/', ':', '<', '>', '@'];
        $escapes = ['20', '22', '26', '27', '2f', '3a', '3c', '3e', '40', '5c'];

        for ($i = 0; $i < strlen($s); $i++) {
            if ($s[$i] === '\\') {
                if (in_array($s[$i + 1] . $s[$i + 2], $escapes)) {
                    $result .= '\\5c';
                } else {
                    $result .= $s[$i];
                }
            } elseif (in_array($s[$i], $chars)) {
                $result .= '\\' . dechex(ord($s[$i]));
            } else {
                $result .= $s[$i];
            }
        }

        return $result;
    }
}
