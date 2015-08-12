<?php

namespace Moxl\Stanza;

class Vcard {
    static function get($to)
    {
        $xml = '<vCard xmlns="vcard-temp"/>';
        $xml = \Moxl\API::iqWrapper($xml, $to, 'get');
        \Moxl\API::request($xml);
    }

    static function set($data)
    {
        $xml = '
            <vCard xmlns="vcard-temp">
                <FN>'.$data->fn->value.'</FN>
                <NICKNAME>'.$data->name->value.'</NICKNAME>
                <URL>'.$data->url->value.'</URL>
                <BDAY>'.$data->date->value.'</BDAY>
                <EMAIL>
                    <USERID>'.$data->email->value.'</USERID>
                </EMAIL>
                <ADR>
                    <LOCALITY>'.$data->locality->value.'</LOCALITY>
                    <PCODE>'.$data->postalcode->value.'</PCODE>
                    <CTRY>'.$data->country->value.'</CTRY>
                </ADR>
                <DESC>'.$data->desc->value.'</DESC>
                <X-GENDER>'.$data->gender->value.'</X-GENDER>
                <MARITAL><STATUS>'.$data->marital->value.'</STATUS></MARITAL>
                <PHOTO>
                    <TYPE>'.$data->phototype->value.'</TYPE>
                    <BINVAL>'.$data->photobin->value.'</BINVAL>
                </PHOTO>
            </vCard>';

        $xml = \Moxl\API::iqWrapper($xml, false, 'set');
        \Moxl\API::request($xml);
    }
}
