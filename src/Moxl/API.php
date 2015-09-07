<?php

namespace Moxl;

class API {
    protected static $xml = '';

    static function iqWrapper($xml = false, $to = false, $type = false, $id = false)
    {
        $session = \Sessionx::start();

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $iq = $dom->createElementNS('jabber:client', 'iq');
        $dom->appendChild($iq);

        if($to != false) {
            $iq->setAttribute('to', $to);
        }

        if($type != false) {
            $iq->setAttribute('type', $type);
        }

        global $language;

        if($id == false) $id = $session->id;
        $iq->setAttribute('id', $id);

        if(isset($language)) {
            $iq->setAttribute('xml:lang', $language);
        }

        if(isset($session->user)) {
            $iq->setAttribute('from', $session->user.'@'.$session->host.'/'.$session->resource);
        }

        if($xml != false) {
            if(is_string($xml)) {
                $f = $dom->createDocumentFragment();
                $f->appendXML($xml);
                $iq->appendChild($f);
            } else {
                $xml = $dom->importNode($xml, true);
                $iq->appendChild($xml);
            }
        }

        return $dom->saveXML($dom->documentElement);
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
        return preg_replace(array("/[\t\r\n]/", '/>\s+</'), array('', "><"), trim(self::$xml));
    }

    /*
     *  Clear the stacked XML
     */
    static function clear()
    {
        self::$xml = '';
    }
}
