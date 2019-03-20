<?php

namespace Moxl\Stanza;

class Vcard4
{
    public static function get($to)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub', 'pubsub');
        $items = $dom->createElement('items');
        $items->setAttribute('node', 'urn:xmpp:vcard4');
        $pubsub->appendChild($items);

        $xml = \Moxl\API::iqWrapper($pubsub, $to, 'get');
        \Moxl\API::request($xml);
    }

    public static function set($data)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub', 'pubsub');

        $publish = $dom->createElement('publish');
        $publish->setAttribute('node', 'urn:xmpp:vcard4');
        $pubsub->appendChild($publish);

        $item = $dom->createElement('item');
        $item->setAttribute('id', 'current');
        $publish->appendChild($item);

        $vcard = $dom->createElementNS('urn:ietf:params:xml:ns:vcard-4.0', 'vcard');
        $item->appendChild($vcard);

        $fn = $dom->createElement('fn');
        $fn->appendChild($dom->createElement('text', $data->fn));
        $vcard->appendChild($fn);

        $nickname = $dom->createElement('nickname');
        $nickname->appendChild($dom->createElement('text', $data->name));
        $vcard->appendChild($nickname);

        $bday = $dom->createElement('bday');
        $bday->appendChild($dom->createElement('date', $data->date));
        $vcard->appendChild($bday);

        $url = $dom->createElement('url');
        $url->appendChild($dom->createElement('uri', $data->url));
        $vcard->appendChild($url);

        $note = $dom->createElement('note');
        $note->appendChild($dom->createElement('text', $data->description));
        $vcard->appendChild($note);

        $impp = $dom->createElement('impp');
        $impp->appendChild($dom->createElement('uri', 'xmpp:'.$data->jid));
        $vcard->appendChild($impp);

        $email = $dom->createElement('email');
        $email->appendChild($dom->createElement('text', $data->email));
        $vcard->appendChild($email);

        $adr = $dom->createElement('adr');
        $adr->appendChild($dom->createElement('locality', $data->adrlocality));
        $adr->appendChild($dom->createElement('code', $data->adrpostalcode));
        $adr->appendChild($dom->createElement('country', $data->adrcountry));
        $vcard->appendChild($adr);

        $xml = \Moxl\API::iqWrapper($pubsub, false, 'set');
        \Moxl\API::request($xml);
    }
}
