<?php

namespace Moxl\Stanza;

class Microblog {
    static function postPublish($to, $id, $content, $name = '')
    {
        $xml = '
            <pubsub xmlns="http://jabber.org/protocol/pubsub">
                <publish node="urn:xmpp:microblog:0">
                <item id="'.$id.'">
                    <entry xmlns="http://www.w3.org/2005/Atom">
                        <author>
                            <name>'.$name.'</name>
                            <uri>xmpp:'.$to.'</uri>
                        </author>
                        
                        <link rel="replies" title="comments" href="xmpp:'.$to.'?;node=urn:xmpp:microblog:0:comments/'.$id.'"/>
                        <content type="text">'.$content.'</content>
                        <published>'.date(DATE_ISO8601).'</published>  
                        <updated>'.date(DATE_ISO8601).'</updated>
                    </entry>
                </item>
                </publish>
            </pubsub>';
        $xml = \Moxl\API::iqWrapper($xml, $to, 'set');
        \Moxl\API::request($xml);
    }

    static function nodeCreate($to) {
        $xml = '
            <pubsub xmlns="http://jabber.org/protocol/pubsub">
                <create node="urn:xmpp:microblog:0"/>
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
            
        $xml = \Moxl\API::iqWrapper($xml, $to, 'set');
        \Moxl\API::request($xml);
    }
        
    static function commentNodeCreate($to, $parentid) {
        $xml = '
            <pubsub xmlns="http://jabber.org/protocol/pubsub">
                <create node="urn:xmpp:microblog:0:comments/'.$parentid.'"/>
                    <configure>
                        <x xmlns="jabber:x:data" type="submit">
                            <field var="FORM_TYPE" type="hidden">
                                <value>http://jabber.org/protocol/pubsub#node_config</value>
                            </field>

                            <field var="pubsub#deliver_notifications">
                                <value>true</value>
                            </field>

                            <field var="pubsub#deliver_payloads">
                                <value>true</value>
                            </field>

                            <field var="pubsub#persist_items">
                                <value>true</value>
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
                                <value>open</value>
                            </field>

                            <field var="pubsub#notify_delete">
                                <value>true</value>
                            </field>

                            <field var="pubsub#notify_retract">
                                <value>true</value>
                            </field>
                            
                            <field var="pubsub#subscribe">
                                <value>true</value>
                            </field>

                            <field var="pubsub#send_last_published_item">
                                <value>on_sub_and_presence</value>
                            </field>


                            <field var="pubsub#notify_sub">
                                <value>true</value>
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
        $xml = \Moxl\API::iqWrapper($xml, $to, 'set');
        \Moxl\API::request($xml);
    }

    static function get($to)
    {
        $xml = '
            <pubsub xmlns="http://jabber.org/protocol/pubsub">
                <items node="urn:xmpp:microblog:0" max_items="40"/>
            </pubsub>';
        $xml = \Moxl\API::iqWrapper($xml, $to, 'get');
        \Moxl\API::request($xml);
    }

    static function commentsGet($to, $id)
    {
        $xml = '
            <pubsub xmlns="http://jabber.org/protocol/pubsub">
                <items node="urn:xmpp:microblog:0:comments/'.$id.'"></items>
            </pubsub>';
        $xml = \Moxl\API::iqWrapper($xml, $to, 'get');
        \Moxl\API::request($xml);
    }

    static function commentPublish($to, $parentid, $content, $name, $from) {
        $xml = '
            <pubsub xmlns="http://jabber.org/protocol/pubsub">
                <publish node="urn:xmpp:microblog:0:comments/'.$parentid.'">
                <item id="'.sha1(date(DATE_ISO8601).$to.$jid.$content).'">
                    <entry xmlns="http://www.w3.org/2005/Atom">
                        <author>
                            <name>'.$name.'</name>
                            <uri>xmpp:'.$from.'</uri>
                        </author>

                        <content type="text">'.$content.'</content>
                        <published>'.date('c').'</published>
                        <updated>'.date('c').'</updated>
                    </entry>
                </item>
                </publish>
            </pubsub>';
        $xml = \Moxl\API::iqWrapper($xml, $to, 'set');
        \Moxl\API::request($xml);
    }
}
