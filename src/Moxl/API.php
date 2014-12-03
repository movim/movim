<?php

namespace Moxl;

class API {
    protected static $xml = '';

    static function iqWrapper($xml, $to = false, $type = false)
    {
        $session = \Sessionx::start();
        
        $toxml = $typexml = '';
        if($to != false)
            $toxml = 'to="'.$to.'"';
        if($type != false)
            $typexml = 'type="'.$type.'"';

        global $language;

        $id = $session->id;

        if(isset($session->user)) {
            $fromxml = 'from="'.$session->user.'@'.$session->host.'/'.$session->ressource.'"';
        } else {
            $fromxml = '';
        }

        return '
            <iq
                id="'.$id.'"
                '.$fromxml.'
                xml:lang="'.$language.'"
                xmlns="jabber:client"
                '.$toxml.'
                '.$typexml.'>
                '.$xml.'
            </iq>
        ';
    }

    /*
     *  Call the request class with the correct XML
     */
    static function request($xml, $type = false)
    {
        self::$xml .= $xml;
    }

    /*
     *  Return the stacked XML and clear it
     */
    static function commit()
    {
        return preg_replace("/[\t\r\n]/", '', trim(self::$xml));
    }
    
    /*
     *  Clear the stacked XML
     */
    static function clear()
    {
        self::$xml = '';
    }
}
