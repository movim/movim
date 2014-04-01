<?php

namespace Moxl\Stanza;

class Group {

    static function get($to, $node)
    {
        $xml = '
            <pubsub xmlns="http://jabber.org/protocol/pubsub">
                <items node="'.$node.'" max_items="40"/>
            </pubsub>';
        $xml = \Moxl\API::iqWrapper($xml, $to, 'get');
        \Moxl\API::request($xml);
    }

    static function subscribe($to, $from, $node)
    {
        $xml = '
            <pubsub xmlns="http://jabber.org/protocol/pubsub">
                <subscribe
                    node="'.$node.'"
                    jid="'.$from.'"/>
            </pubsub>
            ';
            
        $xml = \Moxl\API::iqWrapper($xml, $to, 'set');
        \Moxl\API::request($xml);
    }

    static function unsubscribe($to, $from, $node, $subid)
    {
        if($subid != '')
            $subid = 'subid="'.$subid.'"';
        $xml = '
            <pubsub xmlns="http://jabber.org/protocol/pubsub">
                <unsubscribe
                    node="'.$node.'"
                    jid="'.$from.'"
                    '.$subid.'/>
            </pubsub>';      
        
        $xml = \Moxl\API::iqWrapper($xml, $to, 'set');  
            
        \Moxl\API::request($xml);
    }

    static function getDefaultConfig($to)
    {
        $xml='
            <pubsub xmlns="http://jabber.org/protocol/pubsub#owner">
                <default/>
            </pubsub>';
        $xml = \Moxl\API::iqWrapper($xml, $to, 'get');
        \Moxl\API::request($xml);
    }

    static function create($to, $node)
    {
        $xml = '
            <pubsub xmlns="http://jabber.org/protocol/pubsub">
                <create node="'.$node.'"/>
            </pubsub>';
        $xml = \Moxl\API::iqWrapper($xml, $to, 'set');
        \Moxl\API::request($xml);
    }

    static function getConfigForm($to, $node)
    {
        $xml = '
            <pubsub xmlns="http://jabber.org/protocol/pubsub#owner">
                <configure node="'.$node.'"/>
            </pubsub>';
        
        $xml = \Moxl\API::iqWrapper($xml, $to, 'get');
        \Moxl\API::request($xml);
    }

    static function setConfig($to, $node, $data)
    {
        $xmpp = new \FormtoXMPP();
        $stream = '
            <pubsub xmlns="http://jabber.org/protocol/pubsub#owner">
                <configure node="'.$node.'">
                    <x xmlns="jabber:x:data" type="submit"></x>
                </configure>
            </pubsub>';
        $xml = $xmpp->getXMPP($stream, $data)->asXML();
        $xml = \Moxl\API::iqWrapper(strstr($xml, '<pubsub'), $to, 'set');
        \Moxl\API::request($xml);
    }

    static function getSubscriptions($to, $node)
    {
        $xml .= '
            <pubsub xmlns="http://jabber.org/protocol/pubsub#owner">
                <subscriptions node="'.$node.'"/>
            </pubsub>';
        $xml = \Moxl\API::iqWrapper($xml, $to, 'get');
        \Moxl\API::request($xml);
    }

    static function delete($to, $node)
    {   
        $xml = '<pubsub xmlns="http://jabber.org/protocol/pubsub#owner">
                <delete node="'.$node.'"/>
            </pubsub>';
        $xml = \Moxl\API::iqWrapper($xml, $to, 'set');
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
