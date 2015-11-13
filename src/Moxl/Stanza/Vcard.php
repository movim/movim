<?php

namespace Moxl\Stanza;

class Vcard {
    static function get($to)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $vcard = $dom->createElementNS('vcard-temp', 'vCard');
        $xml = \Moxl\API::iqWrapper($vcard, $to, 'get');
        \Moxl\API::request($xml);
    }

    static function set($data)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $vcard = $dom->createElementNS('vcard-temp', 'vCard');
        $vcard->appendChild($dom->createElement('FN', $data->fn->value));
        $vcard->appendChild($dom->createElement('NICKNAME', $data->name->value));
        $vcard->appendChild($dom->createElement('URL', $data->url->value));
        $vcard->appendChild($dom->createElement('BDAY', $data->date->value));

        $email = $dom->createElement('EMAIL');
        $email->appendChild($dom->createElement('USERID', $data->email->value));
        $vcard->appendChild($email);

        $adr = $dom->createElement('ADR');
        $adr->appendChild($dom->createElement('LOCALITY', $data->locality->value));
        $adr->appendChild($dom->createElement('PCODE', $data->postalcode->value));
        $adr->appendChild($dom->createElement('CTRY', $data->country->value));
        $vcard->appendChild($adr);

        $vcard->appendChild($dom->createElement('DESC', $data->desc->value));
        $vcard->appendChild($dom->createElement('X-GENDER', $data->gender->value));

        $marital = $dom->createElement('MARITAL');
        $marital->appendChild($dom->createElement('STATUS', $data->marital->value));
        $vcard->appendChild($marital);

        $photo = $dom->createElement('PHOTO');
        $photo->appendChild($dom->createElement('TYPE', $data->phototype->value));
        $photo->appendChild($dom->createElement('BINVAL', $data->photobin->value));
        $vcard->appendChild($photo);

        $xml = \Moxl\API::iqWrapper($vcard, false, 'set');
        \Moxl\API::request($xml);
    }
}
