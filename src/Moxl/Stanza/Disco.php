<?php

namespace Moxl\Stanza;

use Moxl\Utils;

class Disco {
    static function answer($to, $id)
    {
        global $session;
        $xml = '
            <iq type="result" xmlns="jabber:client" to="'.$to.'" id="'.$id.'">
                <query 
                    xmlns="http://jabber.org/protocol/disco#info"
                    node="http://moxl.movim.eu/#'.Utils::generateCaps().'">
                    <identity category="client" type="web" name="Movim"/>';

            foreach(Utils::getSupportedServices() as $service)
                $xml .= '<feature var="'.$service.'"/>'."\n";
                
        $xml .= '
                </query>
            </iq>';
        \Moxl\API::request($xml);
    }

    static function request($to, $node)
    {
        $xml = '
            <query xmlns="http://jabber.org/protocol/disco#info"
                node="'.$node.'"/>';
        $xml = \Moxl\API::iqWrapper($xml, $to, 'get');
        \Moxl\API::request($xml); 
    }

    static function items($to)
    {
        $xml = '
            <query xmlns="http://jabber.org/protocol/disco#items"/>';
        $xml = \Moxl\API::iqWrapper($xml, $to, 'get');
        \Moxl\API::request($xml); 
    }
}
