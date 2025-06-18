<?php

namespace Moxl\Stanza;

class Vcard4
{
    public static $node = 'urn:xmpp:vcard4';
    public static $nodeConfig = [
        'FORM_TYPE' => 'http://jabber.org/protocol/pubsub#publish-options',
        'pubsub#persist_items' => 'true',
        'pubsub#access_model' => 'presence',
        'pubsub#send_last_published_item' => 'on_sub_and_presence',
        'pubsub#deliver_payloads' => 'true',
        'pubsub#max_items' => '1',
    ];

    public static function get($to)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub', 'pubsub');
        $items = $dom->createElement('items');
        $items->setAttribute('node', self::$node);
        $pubsub->appendChild($items);

        \Moxl\API::request(\Moxl\API::iqWrapper($pubsub, $to, 'get'));
    }

    public static function set($data, bool $withPublishOption = true)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub', 'pubsub');

        $publish = $dom->createElement('publish');
        $publish->setAttribute('node', self::$node);
        $pubsub->appendChild($publish);

        $item = $dom->createElement('item');
        $item->setAttribute('id', 'current');
        $publish->appendChild($item);

        $vcard = $dom->createElement('vcard');
        $vcard->setAttribute('xmlns', 'urn:ietf:params:xml:ns:vcard-4.0');
        $item->appendChild($vcard);

        if (isset($data->fn)) {
            $fn = $dom->createElement('fn');
            $fn->appendChild($dom->createElement('text', $data->fn));
            $vcard->appendChild($fn);
        }

        if (isset($data->name)) {
            $nickname = $dom->createElement('nickname');
            $nickname->appendChild($dom->createElement('text', $data->name));
            $vcard->appendChild($nickname);
        }

        if (isset($data->date)) {
            $bday = $dom->createElement('bday');
            $bday->appendChild($dom->createElement('date', $data->date));
            $vcard->appendChild($bday);
        }

        if (isset($data->url)) {
            $url = $dom->createElement('url');
            $url->appendChild($dom->createElement('uri', $data->url));
            $vcard->appendChild($url);
        }

        if (isset($data->description)) {
            $note = $dom->createElement('note');
            $note->appendChild($dom->createElement('text', $data->description));
            $vcard->appendChild($note);
        }

        $impp = $dom->createElement('impp');
        $impp->appendChild($dom->createElement('uri', 'xmpp:'.$data->jid));
        $vcard->appendChild($impp);

        if (isset($data->email)) {
            $email = $dom->createElement('email');
            $email->appendChild($dom->createElement('text', $data->email));
            $vcard->appendChild($email);
        }

        if (isset($data->adrcountry) || isset($data->adrlocality) || isset($data->adrpostalcode)) {
            $adr = $dom->createElement('adr');

            if (isset($data->adrlocality)) {
                $adr->appendChild($dom->createElement('locality', $data->adrlocality));
            }

            if (isset($data->adrpostalcode)) {
                $adr->appendChild($dom->createElement('code', $data->adrpostalcode));
            }

            if (isset($data->adrcountry)) {
                $adr->appendChild($dom->createElement('country', $data->adrcountry));
            }

            $vcard->appendChild($adr);
        }

        if ($withPublishOption) {
            $publishOption = $dom->createElement('publish-options');
            $x = $dom->createElement('x');
            $x->setAttribute('xmlns', 'jabber:x:data');
            $x->setAttribute('type', 'submit');
            $publishOption->appendChild($x);

            \Moxl\Utils::injectConfigInX($x, self::$nodeConfig);

            $pubsub->appendChild($publishOption);
        }

        \Moxl\API::request(\Moxl\API::iqWrapper($pubsub, false, 'set'));
    }
}
