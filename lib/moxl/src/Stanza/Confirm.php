<?php

namespace Moxl\Stanza;

use Moxl\Utils;

class Confirm
{
    public static function answer($to, $id, $url, $method, $refuse = false)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $message = $dom->createElementNS('jabber:client', 'message');
        $dom->appendChild($message);
        $message->setAttribute('to', str_replace(' ', '\40', $to));

        $confirm = $dom->createElementNS('http://jabber.org/protocol/http-auth', 'confirm');
        $confirm->setAttribute('id', $id);
        $confirm->setAttribute('url', $url);
        $confirm->setAttribute('method', $method);
        $message->appendChild($confirm);

        if ($refuse) {
            $error = $dom->createElement('error');
            $error->setAttribute('code', 401);
            $error->setAttribute('type', 'auth');
            $confirm->appendChild($error);

            $message->setAttribute('type', 'error');

            $notauth = $dom->createElementNS('urn:ietf:params:xml:xmpp-stanzas', 'not-authorized');
            $error->appendChild($notauth);
        }

        \Moxl\API::request($dom->saveXML($dom->documentElement));
    }
}
