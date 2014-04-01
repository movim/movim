<?php

namespace Moxl\Stanza;

function avatarGet($to)
{
    $xml = '
        <pubsub xmlns="http://jabber.org/protocol/pubsub">
            <items node="urn:xmpp:avatar:data"/>
        </pubsub>';
    $xml = \Moxl\iqWrapper($xml, $to, 'get');
    \Moxl\request($xml);
}

function avatarSet($data)
{
    $xml = '
        <pubsub xmlns="http://jabber.org/protocol/pubsub">
            <publish node="urn:xmpp:avatar:data">
                <item id="'.sha1($data).'">
                    <data xmlns="urn:xmpp:avatar:data">'.$data.'</data>
                </item>
            </publish>
        </pubsub>
    ';
        
    $xml = \Moxl\iqWrapper($xml, false, 'set');
    \Moxl\request($xml);
}
