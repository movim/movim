<?php

namespace Moxl;

use DOMNode;
use Movim\Session;

class API
{
    public static function iqWrapper(?DOMNode $xml = null, $to = false, $type = false, $id = false): string|false
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $iq = $dom->createElementNS('jabber:client', 'iq');
        $dom->appendChild($iq);

        $me = me();

        if ($me->id && $me->session && $me->session->resource) {
            $iq->setAttribute(
                'from',
                $me->id . '/' . $me->session->resource
            );
        }

        if ($to != false) {
            $iq->setAttribute('to', $to);
        }

        if ($type != false) {
            $iq->setAttribute('type', $type);
        }

        global $language;

        if ($id == false) {
            $session = Session::instance();
            $id = $session->get('id');
        }
        $iq->setAttribute('id', $id);

        if (isset($language)) {
            $iq->setAttribute('xml:lang', $language);
        }

        if ($xml != false) {
            $xml = $dom->importNode($xml, true);
            $iq->appendChild($xml);
        }

        return $dom->saveXML($dom->documentElement);
    }

    /**
     * Request a DomDocument
     */
    public static function sendDom(\DOMDocument $dom)
    {
        API::request($dom->saveXML($dom->documentElement));
    }

    /*
     *  Call the request class with the correct XML
     */
    public static function request($xml)
    {
        \writeXMPP($xml);
    }
}
