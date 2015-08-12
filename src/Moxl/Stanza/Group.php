<?php

namespace Moxl\Stanza;

class Group {
    static function getDefaultConfig($to)
    {
        $xml='
            <pubsub xmlns="http://jabber.org/protocol/pubsub#owner">
                <default/>
            </pubsub>';
        $xml = \Moxl\API::iqWrapper($xml, $to, 'get');
        \Moxl\API::request($xml);
    }

    //rename with affiliation ?
    static function getMemberList($to, $node)
    {
        $xml = '<pubsub xmlns="http://jabber.org/protocol/pubsub#owner">
                <affiliations node="'.$node.'"/>
            </pubsub>';
        
        $xml = \Moxl\API::iqWrapper($xml, $to, 'get');
        \Moxl\API::request($xml);  
    }

    static function setMemberListAffiliation($to, $node, $data)
    {
        $affiliations = "";
        foreach($data as $jid_subid => $affiliation){
            $split = split("_", $jid_subid);
            $affiliations .= '<affiliation jid="'.$split[0].'" subid="'.$split[1].'" affiliation="'.$affiliation.'" />';
        }
        
        $xml = '<pubsub xmlns="http://jabber.org/protocol/pubsub#owner">
                <affiliations node="'.$node.'">
                '.$affiliations.'
                </affiliations>
            </pubsub>';
        
        $xml = \Moxl\API::iqWrapper($xml, $to, 'set');
        \Moxl\API::request($xml);  
    }



}
