<?php

namespace Moxl\Stanza;

class Pubsub {
    static function create($to, $node)
    {
        $xml = '
            <pubsub xmlns="http://jabber.org/protocol/pubsub">
                <create node="'.$node.'"/>
            </pubsub>';
        $xml = \Moxl\API::iqWrapper($xml, $to, 'set');
        \Moxl\API::request($xml);
    }

    static function delete($to, $node)
    {   
        $xml = '
            <pubsub xmlns="http://jabber.org/protocol/pubsub#owner">
                <delete node="'.$node.'"/>
            </pubsub>';
        $xml = \Moxl\API::iqWrapper($xml, $to, 'set');
        \Moxl\API::request($xml);    
    }
    
    static function createPersistentStorage($to, $node)
    {
        $xml = '
            <pubsub xmlns="http://jabber.org/protocol/pubsub">
                <create node="'.$node.'"/>
                <configure>
                    <x xmlns="jabber:x:data" type="submit">
                        <field var="FORM_TYPE" type="hidden">
                            <value>http://jabber.org/protocol/pubsub#publish-options</value>
                        </field>
                        <field var="pubsub#persist_items">
                            <value>true</value>
                        </field>
                        <field var="pubsub#access_model">
                            <value>whitelist</value>
                        </field>
                    </x>
                </configure>
            </pubsub>
            ';

        $xml = \Moxl\API::iqWrapper($xml, $to, 'set');
        \Moxl\API::request($xml);
    }
    static function configurePersistentStorage($to, $node)
    {
        $xml = '
            <pubsub xmlns="http://jabber.org/protocol/pubsub#owner">
                <configure node="'.$node.'">
                    <x xmlns="jabber:x:data" type="submit">
                        <field var="FORM_TYPE" type="hidden">
                            <value>http://jabber.org/protocol/pubsub#publish-options</value>
                        </field>
                        <field var="pubsub#persist_items">
                            <value>true</value>
                        </field>
                        <field var="pubsub#access_model">
                            <value>whitelist</value>
                        </field>
                    </x>
                </configure>
            </pubsub>
            ';

        $xml = \Moxl\API::iqWrapper($xml, $to, 'set');
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

    static function getSubscriptions($to, $node)
    {
        $xml = '
            <pubsub xmlns="http://jabber.org/protocol/pubsub#owner">
                <subscriptions node="'.$node.'"/>
            </pubsub>';
        $xml = \Moxl\API::iqWrapper($xml, $to, 'get');
        \Moxl\API::request($xml);
    }

    static function setSubscriptions($to, $node, $data)
    {
        $subscriptions = "";
        foreach($data as $jid_subid => $subscription){
            list($jid, $subid) = split("_", $jid_subid);
            $subscriptions .= '
                <subscription
                    jid="'.$jid.'" ';
                if($subid != null)
                    $subscriptions .=
                        'subid="'.$subid.'" ';
                $subscriptions .= '
                    subscription="'.$subscription.'" />';
        }
        
        $xml = '
            <pubsub xmlns="http://jabber.org/protocol/pubsub#owner">
                <subscriptions node="'.$node.'">
                '.$subscriptions.'
                </subscriptions>
            </pubsub>';
        
        $xml = \Moxl\API::iqWrapper($xml, $to, 'set');
        \Moxl\API::request($xml);  
    }

    static function getItems($to, $node)
    {
        $xml = '
            <pubsub xmlns="http://jabber.org/protocol/pubsub">
                <items node="'.$node.'" max_items="40"/>
            </pubsub>';
        $xml = \Moxl\API::iqWrapper($xml, $to, 'get');
        \Moxl\API::request($xml);
    }

    static function getItem($to, $node, $id)
    {
        $xml = '
            <pubsub xmlns="http://jabber.org/protocol/pubsub">
                <items node="'.$node.'">
                    <item id="'.$id.'"/>
                </items>
            </pubsub>';
        $xml = \Moxl\API::iqWrapper($xml, $to, 'get');
        \Moxl\API::request($xml);        
    }

    static function postPublish($to, $node, $atom)
    {
        $xml = '
            <pubsub xmlns="http://jabber.org/protocol/pubsub">
                <publish node="'.$node.'">
                <item id="'.$atom->id.'">
                    '.$atom.'
                </item>
                </publish>
            </pubsub>';
        $xml = \Moxl\API::iqWrapper($xml, $to, 'set');
        \Moxl\API::request($xml);
    }

    static function postDelete($to, $node, $id)
    {
        $xml = '
            <pubsub xmlns="http://jabber.org/protocol/pubsub">
                <retract node="'.$node.'" notify="true">
                    <item id="'.$id.'"/>
                </retract>
            </pubsub>';
        $xml = \Moxl\API::iqWrapper($xml, $to, 'set');
        \Moxl\API::request($xml);
    }

    static function getConfig($to, $node)
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

    static function getAffiliations($to, $node)
    {
        $xml = '
            <pubsub xmlns="http://jabber.org/protocol/pubsub#owner">
                <affiliations node="'.$node.'"/>
            </pubsub>';
        
        $xml = \Moxl\API::iqWrapper($xml, $to, 'get');
        \Moxl\API::request($xml);  
    }

    static function getMetadata($to, $node)
    {
        $xml = '
            <query xmlns="http://jabber.org/protocol/disco#info"
            node="'.$node.'"/>
        ';
        
        $xml = \Moxl\API::iqWrapper($xml, $to, 'get');
        \Moxl\API::request($xml);  
    }

    static function setAffiliations($to, $node, $data)
    {
        $affiliations = "";
        foreach($data as $jid_subid => $affiliation){
            $split = split("_", $jid_subid);
            $affiliations .= '
                <affiliation 
                    jid="'.$split[0].'" 
                    subid="'.$split[1].'" 
                    affiliation="'.$affiliation.'" />';
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
