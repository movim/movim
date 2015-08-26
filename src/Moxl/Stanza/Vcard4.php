<?php

namespace Moxl\Stanza;

class Vcard4 {
    static function get($to)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub', 'pubsub');
        $items = $dom->createElement('items');
        $items->setAttribute('node', 'urn:xmpp:vcard4');
        $pubsub->appendChild($items);

        $xml = \Moxl\API::iqWrapper($pubsub, $to, 'get');
        \Moxl\API::request($xml);
    }

    static function set($data)
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

        $gender = $dom->createElement('gender');
        $sex = $dom->createElement('sex');
        $gender->appendChild($sex);
        $sex->appendChild($dom->createElement('text', $data->gender));
        $vcard->appendChild($gender);

        $marital = $dom->createElement('marital');
        $status = $dom->createElement('status');
        $marital->appendChild($status);
        $status->appendChild($dom->createElement('text', $data->marital));
        $vcard->appendChild($gender);

        $impp = $dom->createElement('impp');
        $impp->appendChild($dom->createElement('uri', 'xmpp:'.$data->jid));
        if($data->twitter)
            $impp->appendChild($dom->createElement('uri', 'twitter:'.$data->twitter));
        if($data->yahoo)
            $impp->appendChild($dom->createElement('uri', 'ymsgr:'.$data->yahoo));
        if($data->skype)
            $impp->appendChild($dom->createElement('uri', 'skype:'.$data->skype));
        $vcard->appendChild($impp);

        $email = $dom->createElement('email');
        $email->appendChild($dom->createElement('text', $data->email));
        $vcard->appendChild($email);

        $adr = $dom->createElement('ard');
        $adr->appendChild($dom->createElement('locality', $data->adrlocality));
        $adr->appendChild($dom->createElement('code', $data->adrpostalcode));
        $adr->appendChild($dom->createElement('country', $data->adrcountry));
        $vcard->appendChild($note);

        $xml = \Moxl\API::iqWrapper($pubsub, false, 'set');
        \Moxl\API::request($xml);
    }
}
