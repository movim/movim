<?php

namespace Moxl\Stanza;

use Moxl\Stanza\Form;

class Register {
    static function get($to = false)
    {
        $xml = '<query xmlns="jabber:iq:register"/>';
        $xml = \Moxl\API::iqWrapper($xml, $to, 'get');
        \Moxl\API::request($xml);
    }
    static function set($to = false, $data)
    {
        $form = new Form($data);
        
        $xml = '
            <query xmlns="jabber:iq:register">
                '.$form.'
            </query>
            ';
        $xml = \Moxl\API::iqWrapper($xml, $to, 'set');
        \Moxl\API::request($xml);
    }

    static function remove()
    {
        $xml = '
            <query xmlns="jabber:iq:register">
                <remove/>
            </query>';
        $xml = \Moxl\API::iqWrapper($xml, false, 'set');
        \Moxl\API::request($xml);
    }

    static function changePassword($to, $username, $password)
    {
        $xml = '
            <query xmlns="jabber:iq:register">
                <username>'.$username.'</username>
                <password>'.$password.'</password>
            </query>';
        $xml = \Moxl\API::iqWrapper($xml, $to, 'set');
        \Moxl\API::request($xml);
    }
}
