<?php

namespace Moxl\Stanza;

/*
 * The roster builder
 */
function rosterBuilder($xml, $to, $type)
{  
    $xml = '
        <query xmlns="jabber:iq:roster">
            '.$xml.'
        </query>';
        
    $xml = \Moxl\iqWrapper($xml, $to, $type);
    \Moxl\request($xml);
}

function rosterGet()
{
    $xml = '<query xmlns="jabber:iq:roster"/>';
    
    $xml = \Moxl\iqWrapper($xml, false, 'get');
    \Moxl\request($xml);
}

/*
 * Add contact
 */
function rosterAdd($to, $name, $group)
{
    $xml ='
        <item
            jid="'.$to.'"
            name="'.$name.'">
            <group>'.$group.'</group>
        </item>';
    
    $xml = rosterBuilder($xml, false, 'set');
    \Moxl\request($xml);
}

function rosterUpdate($to, $name, $group)
{
    $xml = rosterAdd($to, $name, $group);
    \Moxl\request($xml);
}

/*
 * Remove a contact
 */
function rosterRemove($to)
{
    $xml = '
        <item jid="'.$to.'" subscription="remove"/>';
        
    $xml = rosterBuilder($xml, false, 'set');
    \Moxl\request($xml);
}
