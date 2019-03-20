<?php

namespace Moxl\Stanza;

class Vcard
{
    public static function get($to)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $vcard = $dom->createElementNS('vcard-temp', 'vCard');
        $xml = \Moxl\API::iqWrapper($vcard, $to, 'get');
        \Moxl\API::request($xml);
    }

    public static function set($to = false, $data)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $vcard = $dom->createElementNS('vcard-temp', 'vCard');

        if (isset($data->fn)) {
            $vcard->appendChild($dom->createElement('FN', $data->fn->value));
        }
        if (isset($data->name)) {
            $vcard->appendChild($dom->createElement('NICKNAME', $data->name->value));
        }
        if (isset($data->url)) {
            $vcard->appendChild($dom->createElement('URL', $data->url->value));
        }
        if (isset($data->date)) {
            $vcard->appendChild($dom->createElement('BDAY', $data->date->value));
        }

        if (isset($data->email)) {
            $email = $dom->createElement('EMAIL');
            $email->appendChild($dom->createElement('USERID', $data->email->value));
            $vcard->appendChild($email);
        }

        if (isset($data->country) || isset($data->locality) || isset($data->postalcode)) {
            $adr = $dom->createElement('ADR');
            $adr->appendChild($dom->createElement('LOCALITY', $data->locality->value));
            $adr->appendChild($dom->createElement('PCODE', $data->postalcode->value));
            $adr->appendChild($dom->createElement('CTRY', $data->country->value));
            $vcard->appendChild($adr);
        }

        if (isset($data->desc)) {
            $vcard->appendChild($dom->createElement('DESC', $data->desc->value));
        }

        if (isset($data->photobin) && isset($data->phototype)) {
            $photo = $dom->createElement('PHOTO');
            $photo->appendChild($dom->createElement('TYPE', $data->phototype->value));
            $photo->appendChild($dom->createElement('BINVAL', $data->photobin->value));
            $vcard->appendChild($photo);
        }

        $xml = \Moxl\API::iqWrapper($vcard, $to, 'set');
        \Moxl\API::request($xml);
    }
}
