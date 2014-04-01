<?php

namespace Moxl\Stanza;

function pubsubSubscriptionListNodeCreate($to) {
    $xml = '
        <pubsub xmlns="http://jabber.org/protocol/pubsub">
            <create node="urn:xmpp:pubsub:subscription"/>
            <configure>
                <x xmlns="jabber:x:data" type="submit">
                    <field var="FORM_TYPE" type="hidden">
                        <value>http://jabber.org/protocol/pubsub#node_config</value>
                    </field>

                    <field var="pubsub#deliver_notifications">
                        <value>1</value>
                    </field>

                    <field var="pubsub#deliver_payloads">
                        <value>1</value>
                    </field>

                    <field var="pubsub#persist_items">
                        <value>1</value>
                    </field>

                    <field var="pubsub#max_items">
                        <value>100</value>
                    </field>

                    <field var="pubsub#item_expire">
                        <value>604800</value>
                    </field>

                    <field var="pubsub#access_model">
                        <value>open</value>
                    </field>

                    <field var="pubsub#publish_model">
                        <value>publishers</value>
                    </field>

                    <field var="pubsub#purge_offline">
                        <value>0</value>
                    </field>

                    <field var="pubsub#notify_config">
                        <value>0</value>
                    </field>

                    <field var="pubsub#notify_delete">
                        <value>0</value>
                    </field>

                    <field var="pubsub#notify_retract">
                        <value>0</value>
                    </field>
                    
                    
                    <field var="pubsub#subscribe" type="boolean">
                        <value>1</value>
                    </field>

                    <field var="pubsub#send_last_published_item" type="list-single">
                        <value>on_sub_and_presence</value>
                    </field>


                    <field var="pubsub#notify_sub">
                        <value>1</value>
                    </field>

                    <field var="pubsub#type">
                        <value>http://www.w3.org/2005/Atom</value>
                    </field>

                    <field var="pubsub#body_xslt">
                        <value>http://jabxslt.jabberstudio.org/atom_body.xslt</value>
                    </field>
                </x>
            </configure>
        </pubsub>';
        
    $xml = \Moxl\iqWrapper($xml, $to, 'set');
    \Moxl\request($xml);
}

function pubsubSubscriptionListAdd($server, $jid, $node, $title)
{
    $id = "";
    $id .= $server.'<';
    $id .= $node.'<';
    $id .= $jid;
    $id = sha1($id);
    
    $xml .= '
        <pubsub xmlns="http://jabber.org/protocol/pubsub">
            <publish node="urn:xmpp:pubsub:subscription">
              <item id="'.$id.'">
                <subscription xmlns="urn:xmpp:pubsub:subscription:0"
                    server="'.$server.'" node="'.$node.'">
                  <title>'.$title.'</title>
                </subscription>
              </item>
            </publish>
        </pubsub>
        ';
    $xml = \Moxl\iqWrapper($xml, false, 'set');
    \Moxl\request($xml);
}

function pubsubSubscriptionListRemove($server, $jid, $node)
{
    $id = "";
    $id .= $server.'<';
    $id .= $node.'<';
    $id .= $jid;
    $id = sha1($id);
    
    $xml .= '
        <pubsub xmlns="http://jabber.org/protocol/pubsub">
            <retract node="urn:xmpp:pubsub:subscription">
              <item id="'.$id.'"/>
            </retract>
        </pubsub>
        ';
    $xml = \Moxl\iqWrapper($xml, false, 'set');
    \Moxl\request($xml);
}

function pubsubSubscriptionListGet() {
    $xml = '<pubsub xmlns="http://jabber.org/protocol/pubsub">
            <items node="urn:xmpp:pubsub:subscription"/>
        </pubsub>';
        
    $xml = \Moxl\iqWrapper($xml, false, 'get');
    \Moxl\request($xml);
}

function pubsubSubscriptionListGetOwned() {
    $xml = '<pubsub xmlns="http://jabber.org/protocol/pubsub">
            <affiliations/>
        </pubsub>';
        
    $xml = \Moxl\iqWrapper($xml, false, 'get');
    \Moxl\request($xml);
}

function pubsubSubscriptionListGetFriends($to) {
    $xml = '
        <pubsub xmlns="http://jabber.org/protocol/pubsub">
            <items node="urn:xmpp:pubsub:subscription"/>
        </pubsub>';
    
    $xml = \Moxl\iqWrapper($xml, $to, 'get');
    \Moxl\request($xml);
}
