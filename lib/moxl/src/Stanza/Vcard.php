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

    public static function set($to, $data)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $vcard = $dom->createElementNS('vcard-temp', 'vCard');

        if (isset($data->fn)) {
            $vcard->appendChild($dom->createElement('FN', $data->fn));
        }
        if (isset($data->name)) {
            $vcard->appendChild($dom->createElement('NICKNAME', $data->name));
        }
        if (isset($data->url)) {
            $vcard->appendChild($dom->createElement('URL', $data->url));
        }
        if (isset($data->date)) {
            $vcard->appendChild($dom->createElement('BDAY', $data->date));
        }

        if (isset($data->email)) {
            $email = $dom->createElement('EMAIL');
            $email->appendChild($dom->createElement('USERID', $data->email));
            $vcard->appendChild($email);
        }

        if (isset($data->adrcountry) || isset($data->adrlocality) || isset($data->adrpostalcode)) {
            $adr = $dom->createElement('ADR');

            if (isset($data->adrlocality)) {
                $adr->appendChild($dom->createElement('LOCALITY', $data->adrlocality));
            }

            if (isset($data->adrpostalcode)) {
                $adr->appendChild($dom->createElement('PCODE', $data->adrpostalcode));
            }

            if (isset($data->adrcountry)) {
                $adr->appendChild($dom->createElement('CTRY', $data->adrcountry));
            }

            $vcard->appendChild($adr);
        }

        if (isset($data->desc)) {
            $vcard->appendChild($dom->createElement('DESC', $data->desc));
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
