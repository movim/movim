<?php

namespace Moxl\Stanza;

class Upload {
    static function request($to, $name, $size, $type)
    {
        $xml = '
            <request xmlns="eu:siacs:conversations:http:upload">
                <filename>'.$name.'</filename>
                <size>'.$size.'</size>
                <content-type>'.$type.'</content-type>
            </request>';

        $xml = \Moxl\API::iqWrapper($xml, $to, 'get');
        \Moxl\API::request($xml);
    }
}
