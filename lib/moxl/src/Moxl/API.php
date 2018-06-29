<?php

namespace Moxl;

use Movim\Session;

class API
{
    static function iqWrapper($xml = false, $to = false, $type = false, $id = false)
    {
        $session = Session::start();

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $iq = $dom->createElementNS('jabber:client', 'iq');
        $dom->appendChild($iq);

        $iq->setAttribute(
            'from',
            $session->get('jid').'/'.$session->get('resource'));

        if($to != false) {
            $iq->setAttribute('to', $to);
        }

        if($type != false) {
            $iq->setAttribute('type', $type);
        }

        global $language;

        if($id == false) $id = $session->get('id');
        $iq->setAttribute('id', $id);

        if(isset($language)) {
            $iq->setAttribute('xml:lang', $language);
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
    static function request($xml)
    {
        writeXMPP($xml);
    }
}
