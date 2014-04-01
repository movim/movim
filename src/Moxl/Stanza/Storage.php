<?php
/*
 * Basic stanza for the XEP-0049 implementation
 */ 

namespace Moxl\Stanza;

function storageSet($xmlns, $data)
{
    $xml = '
        <query xmlns="jabber:iq:private">
            <data xmlns="'.$xmlns.'">
                '.$data.'
            </data>
        </query>';
    $xml = \Moxl\iqWrapper($xml, false, 'set');
    \Moxl\request($xml);
}

function storageGet($xmlns)
{
    $xml = '
        <query xmlns="jabber:iq:private">
            <data xmlns="'.$xmlns.'"/>
        </query>';
    $xml = \Moxl\iqWrapper($xml, false, 'get');
    \Moxl\request($xml);
}
