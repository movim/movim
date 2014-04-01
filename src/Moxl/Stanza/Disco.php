<?php

namespace Moxl\Stanza;

use Moxl\Utils;

function discoAnswer($to, $id)
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
    \Moxl\request($xml);
}

function discoRequest($to, $node)
{
    $xml = '
        <query xmlns="http://jabber.org/protocol/disco#info"
            node="'.$node.'"/>';
    $xml = \Moxl\iqWrapper($xml, $to, 'get');
    \Moxl\request($xml); 
}

function discoItems($to)
{
    $xml = '
        <query xmlns="http://jabber.org/protocol/disco#items"/>';
    $xml = \Moxl\iqWrapper($xml, $to, 'get');
    \Moxl\request($xml); 
}
