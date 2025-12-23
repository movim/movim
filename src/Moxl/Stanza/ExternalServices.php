<?php

namespace Moxl\Stanza;

class ExternalServices
{
    public static function request()
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('urn:xmpp:extdisco:2', 'services');

        return $query;
    }
}
