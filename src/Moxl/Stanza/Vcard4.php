<?php

namespace Moxl\Stanza;

function vcard4Get($to)
{
    $xml = '
        <pubsub xmlns="http://jabber.org/protocol/pubsub">
            <items node="urn:xmpp:vcard4"/>
        </pubsub>';
    $xml = \Moxl\iqWrapper($xml, $to, 'get');
    \Moxl\request($xml);
}

function vcard4Set($data)
{
    $twitter = $yahoo = $skype = '';
    
    if($data->twitter)
        $twitter = '<uri>twitter:'.$data->twitter.'</uri>';
    if($data->yahoo)
        $yahoo = '<uri>ymsgr:'.$data->yahoo.'</uri>';
    if($data->skype)
        $skype = '<uri>skype:'.$data->skype.'</uri>';
    
    $xml = '
        <pubsub xmlns="http://jabber.org/protocol/pubsub">
            <publish node="urn:xmpp:vcard4">
                <item id="current">
                    <vcard xmlns="urn:ietf:params:xml:ns:vcard-4.0">
                        <fn><text>'.$data->fn.'</text></fn>
                        <nickname><text>'.$data->name.'</text></nickname>
                        <bday><date>'.$data->date.'</date></bday>
                        <url><uri>'.$data->url.'</uri></url>
                        <note>
                            <text>
                                '.$data->description.'
                            </text>
                        </note>
                        <gender>
                            <sex>
                                <text>'.$data->gender.'</text>
                            </sex>
                        </gender>
                        <marital>
                            <status>
                                <text>'.$data->marital.'</text>
                            </status>
                        </marital>
                        <impp>
                            <uri>xmpp:'.$data->jid.'</uri>
                            '.$twitter.'
                            '.$yahoo.'
                            '.$skype.'
                        </impp>
                        <email>
                            <text>'.$data->email.'</text>
                        </email>

                        <adr>
                            <locality>'.$data->adrlocality.'</locality>
                            <code>'.$data->adrpostalcode.'</code>
                            <country>'.$data->adrcountry.'</country>
                        </adr>
                    </vcard>
                </item>
            </publish>
        </pubsub>';
        
    $xml = \Moxl\iqWrapper($xml, false, 'set');
    \Moxl\request($xml);
}
