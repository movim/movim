<?php

namespace Moxl\Stanza;

class Ack {
    static function send($to, $id)
    {
        $xml = '
            <iq 
                type="result" 
                xmlns="jabber:client" 
                to="'.$to.'" 
                id="'.$id.'"/>';

        \Moxl\API::request($xml);
    }
}
