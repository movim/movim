<?php

namespace Moxl\Stanza;

function groupGet($to, $node)
{
    $xml = '
        <pubsub xmlns="http://jabber.org/protocol/pubsub">
            <items node="'.$node.'" max_items="40"/>
        </pubsub>';
    $xml = \Moxl\iqWrapper($xml, $to, 'get');
    \Moxl\request($xml);
}

function groupSubscribe($to, $from, $node)
{
    $xml = '
        <pubsub xmlns="http://jabber.org/protocol/pubsub">
            <subscribe
                node="'.$node.'"
                jid="'.$from.'"/>
        </pubsub>
        ';
        
    $xml = \Moxl\iqWrapper($xml, $to, 'set');
    \Moxl\request($xml);
}

function groupUnsubscribe($to, $from, $node, $subid)
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
    
    $xml = \Moxl\iqWrapper($xml, $to, 'set');  
        
    \Moxl\request($xml);
}

function groupGetDefaultConfig($to)
{
    $xml='
        <pubsub xmlns="http://jabber.org/protocol/pubsub#owner">
            <default/>
        </pubsub>';
    $xml = \Moxl\iqWrapper($xml, $to, 'get');
    \Moxl\request($xml);
}

function groupCreate($to, $node)
{
    $xml = '
        <pubsub xmlns="http://jabber.org/protocol/pubsub">
            <create node="'.$node.'"/>
        </pubsub>';
    $xml = \Moxl\iqWrapper($xml, $to, 'set');
    \Moxl\request($xml);
}

function groupGetConfigForm($to, $node)
{
    $xml = '
        <pubsub xmlns="http://jabber.org/protocol/pubsub#owner">
            <configure node="'.$node.'"/>
        </pubsub>';
    
    $xml = \Moxl\iqWrapper($xml, $to, 'get');
    \Moxl\request($xml);
}

function groupSetConfig($to, $node, $data)
{
    $xmpp = new \FormtoXMPP();
    $stream = '
        <pubsub xmlns="http://jabber.org/protocol/pubsub#owner">
            <configure node="'.$node.'">
                <x xmlns="jabber:x:data" type="submit"></x>
            </configure>
        </pubsub>';
    $xml = $xmpp->getXMPP($stream, $data)->asXML();
    $xml = \Moxl\iqWrapper(strstr($xml, '<pubsub'), $to, 'set');
    \Moxl\request($xml);
}

function groupGetSubscriptions($to, $node)
{
    $xml .= '
        <pubsub xmlns="http://jabber.org/protocol/pubsub#owner">
            <subscriptions node="'.$node.'"/>
        </pubsub>';
    $xml = \Moxl\iqWrapper($xml, $to, 'get');
    \Moxl\request($xml);
}

function groupDelete($to, $node)
{   
    $xml = '<pubsub xmlns="http://jabber.org/protocol/pubsub#owner">
            <delete node="'.$node.'"/>
        </pubsub>';
    $xml = \Moxl\iqWrapper($xml, $to, 'set');
    \Moxl\request($xml);    
}

//rename with affiliation ?
function groupGetMemberList($to, $node)
{
    $xml = '<pubsub xmlns="http://jabber.org/protocol/pubsub#owner">
            <affiliations node="'.$node.'"/>
        </pubsub>';
    
    $xml = \Moxl\iqWrapper($xml, $to, 'get');
    \Moxl\request($xml);  
}

function groupSetMemberListAffiliation($to, $node, $data)
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
    
    $xml = \Moxl\iqWrapper($xml, $to, 'set');
    \Moxl\request($xml);  
}


