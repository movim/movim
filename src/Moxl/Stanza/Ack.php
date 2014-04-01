<?php

namespace Moxl\Stanza;

function ackSend($to, $id)
{
    $xml = '
        <iq 
            type="result" 
            xmlns="jabber:client" 
            to="'.$to.'" 
            id="'.$id.'"/>';

    \Moxl\request($xml);
}
