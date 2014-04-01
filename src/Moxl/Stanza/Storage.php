<?php
/*
 * Basic stanza for the XEP-0049 implementation
 */ 

namespace Moxl\Stanza;

class Storage {
    static function set($xmlns, $data)
    {
        $xml = '
            <query xmlns="jabber:iq:private">
                <data xmlns="'.$xmlns.'">
                    '.$data.'
                </data>
            </query>';
        $xml = \Moxl\API::iqWrapper($xml, false, 'set');
        \Moxl\API::request($xml);
    }

    static function get($xmlns)
    {
        $xml = '
            <query xmlns="jabber:iq:private">
                <data xmlns="'.$xmlns.'"/>
            </query>';
        $xml = \Moxl\API::iqWrapper($xml, false, 'get');
        \Moxl\API::request($xml);
    }

}
